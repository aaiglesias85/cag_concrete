<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Entity\Company;
use App\Entity\Usuario;
use App\Entity\Equation;
use App\Entity\Item;
use App\Entity\Unit;
use App\Service\Admin\WidgetAccessService;
use App\Utils\Admin\AdvertisementService;
use App\Utils\Admin\DefaultService;
use App\Utils\Admin\EstimateService;
use App\Utils\Admin\DataTrackingService;
use App\Utils\Admin\LogService;
use App\Utils\Admin\NotificationService;
use App\Utils\Admin\ScheduleService;
use App\Utils\Admin\TaskService;
use App\Service\Admin\AdminAccessService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractAdminController
{
   private $defaultService;
   private $logService;
   private $notificationService;
   private $advertisementService;
   private TaskService $taskService;
   private ScheduleService $scheduleService;
   private EstimateService $estimateService;
   private DataTrackingService $dataTrackingService;
   private WidgetAccessService $widgetAccessService;

   public function __construct(
      AdminAccessService $adminAccess,
      DefaultService                  $defaultService,
      LogService                      $logService,
      NotificationService             $notificationService,
      AdvertisementService            $advertisementService,
      TaskService                     $taskService,
      ScheduleService                 $scheduleService,
      EstimateService                 $estimateService,
      DataTrackingService             $dataTrackingService,
      WidgetAccessService            $widgetAccessService
   ) {
      parent::__construct($adminAccess);
      $this->defaultService = $defaultService;
      $this->logService = $logService;
      $this->notificationService = $notificationService;
      $this->advertisementService = $advertisementService;
      $this->taskService = $taskService;
      $this->scheduleService = $scheduleService;
      $this->estimateService = $estimateService;
      $this->dataTrackingService = $dataTrackingService;
      $this->widgetAccessService = $widgetAccessService;
   }

   public function index()
   {
      $acceso = $this->adminAccess->exigirUsuarioYPermisoVer($this->getUser(), FunctionId::HOME);
      if ($acceso instanceof RedirectResponse) {
         return $acceso;
      }
      $usuario = $acceso['usuario'];

      $dashboardWidgets = $this->defaultService->ObtenerWidgetsDashboardV3(
         $usuario->getUsuarioId()
      );
      $homePayloads = $this->defaultService->construirPayloadsWidgetsHome($usuario, $dashboardWidgets);

      return $this->render('admin/default/index.html.twig', \array_merge([
         'usuario' => $usuario,
         'dashboard_widgets' => $dashboardWidgets,
      ], $homePayloads));
   }

   public function widgetPreferences(): Response
   {
      $u = $this->adminAccess->exigirUsuarioOlogin($this->getUser());
      if ($u instanceof RedirectResponse) {
         return $u;
      }
      $usuario = $u;
      $widgets = $this->defaultService->ObtenerMyWidgetsTogglesV3($usuario->getUsuarioId());

      return $this->render('admin/default/widget_preferences.html.twig', [
         'usuario' => $usuario,
         'widgets' => $widgets,
         'urlSave' => $this->generateUrl('saveUserWidgetPreference'),
      ]);
   }

   public function saveWidgetPreference(Request $request): \Symfony\Component\HttpFoundation\JsonResponse
   {
      $g = $this->adminAccess->exigirUsuarioOlogin($this->getUser());
      if ($g instanceof RedirectResponse) {
         return $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
      }
      $usuario = $g;
      $widgetId  = $request->request->get('widget_id', '');
      $isActive  = filter_var($request->request->get('is_active', true), FILTER_VALIDATE_BOOLEAN);

      if ($widgetId === '') {
         return $this->json(['success' => false, 'message' => 'Missing widget_id'], 400);
      }

      try {
         $this->widgetAccessService->setUserWidgetFromMyWidgetsPage(
            $usuario->getUsuarioId(),
            (string) $widgetId,
            $isActive
         );
      } catch (\InvalidArgumentException $e) {
         return $this->json(['success' => false, 'message' => $e->getMessage()], 403);
      }

      return $this->json(['success' => true]);
   }

   /**
    * listarStats Acción para filtrar el dashboard
    *
    */
   public function listarStats(Request $request)
   {
      $g = $this->adminAccess->exigirUsuarioOlogin($this->getUser());
      if ($g instanceof RedirectResponse) {
         return $this->json([
            'success' => false,
            'error' => 'Unauthenticated',
         ], 401);
      }
      $usuario = $g;
      $userId = $usuario->getUsuarioId();
      $w = $this->widgetAccessService;
      $projectId = (string) ($request->get('project_id') ?? '');
      $status = (string) ($request->get('status') ?? '');
      $fechaInicial = (string) ($request->get('fechaInicial') ?? '');
      $fechaFin = (string) ($request->get('fechaFin') ?? '');

      try {
         $stats = [];

         if ($w->isWidgetEnabledForUser($userId, 'tasks')) {
            $pTaskA = $this->defaultService->BuscarPermiso($userId, FunctionId::TASKS);
            $pTask = $pTaskA[0] ?? [
               'ver' => false,
               'agregar' => false,
               'editar' => false,
               'eliminar' => false,
               'funcion_id' => FunctionId::TASKS,
               'permiso_id' => 0,
            ];
            $stats['tasks'] = $this->taskService->listarTareasPayloadHome(
               $usuario,
               $pTask,
               $fechaInicial,
               $fechaFin
            );
         }

         if ($w->isWidgetEnabledForUser($userId, 'work_schedule')) {
            $stats['work_schedule'] = $this->scheduleService->listarSchedulesPayloadHome(
               $fechaInicial,
               $fechaFin,
               30,
               $projectId
            );
         }

         if ($w->isWidgetEnabledForUser($userId, 'bid_deadlines')) {
            $stats['bid_deadlines'] = $this->estimateService->listarUpcomingBidDeadlinesPayloadHome(
               $fechaInicial,
               $fechaFin,
               0,
               ''
            );
         }

         if ($w->isWidgetEnabledForUser($userId, 'current_month_data_tracking')) {
            $stats['current_month_data_tracking'] = $this->dataTrackingService->listarCurrentMonthProjectsPayloadHome(
               $projectId,
               $fechaInicial,
               $fechaFin
            );
         }

         if ($w->isWidgetEnabledForUser($userId, 'pay_item_totals')) {
            $stats['pay_item_totals'] = $this->defaultService->ListarItemsConMontos(
               $projectId,
               $fechaInicial,
               $fechaFin,
               $status
            );
         }

         if ($w->isWidgetEnabledForUser($userId, 'invoiced_projects')) {
            $stats['invoiced_projects'] = $this->defaultService->ListarInvoicedProjectsPayloadHome(
               $projectId,
               $fechaInicial,
               $fechaFin
            );
         }

         if ($w->isWidgetEnabledForUser($userId, 'estimate_win_loss')) {
            $stats['chart_estimate_win_loss'] = $this->defaultService->DevolverDataChartEstimateWinLoss(
               '',
               $fechaInicial,
               $fechaFin
            );
         }
         if ($w->isWidgetEnabledForUser($userId, 'estimates_submitted_totals')) {
            $stats['chart_estimates_submitted_totals'] = $this->defaultService->DevolverDataChartEstimateSubmittedTotals(
               '',
               $fechaInicial,
               $fechaFin
            );
         }
         if ($w->isWidgetEnabledForUser($userId, 'estimator_submitted_share')) {
            $stats['chart_estimator_submitted_share'] = $this->defaultService->DevolverDataChartEstimatorSubmittedShare(
               '',
               $fechaInicial,
               $fechaFin
            );
         }
         if ($w->isWidgetEnabledForUser($userId, 'invoice_profit_share')) {
            $stats['chart_profit'] = $this->defaultService->DevolverDataChartProfit(
               $projectId,
               $fechaInicial,
               $fechaFin,
               $status
            );
         }
         if ($w->isWidgetEnabledForUser($userId, 'job_cost_breakdown')) {
            $stats['chart_costs'] = $this->defaultService->DevolverDataChartCosts(
               $projectId,
               $fechaInicial,
               $fechaFin,
               $status
            );
         }

         $resultadoJson = [
            'success' => true,
            'stats' => $stats,
         ];

         return $this->json($resultadoJson);
      } catch (\Exception $e) {
         $resultadoJson = [
            'success' => false,
            'error' => $e->getMessage(),
         ];

         return $this->json($resultadoJson);
      }
   }

   public function renderHeader()
   {
      $usuario = $this->getUser();

      $logs = [];
      $notificaciones = [];
      if ($usuario != null) {
         $logs = $this->logService->ListarLogsUltimosDias($usuario);

         $notificaciones = $this->notificationService->ListarNotificationsUltimosDias($usuario);

         // contar sin leer
         $sin_leer = 0;
         foreach ($notificaciones as $value) {
            if (!$value['leida']) {
               $sin_leer++;
            }
         }
      }

      $advertisements = $this->advertisementService->ListarAdvertisementsUltimosDias();

      return $this->render('admin/layout/header.html.twig', array(
         'usuario' => $usuario,
         'logs' => $logs,
         'notificaciones' => $notificaciones,
         'advertisements' => $advertisements,
         'notificaciones_sin_leer' => $sin_leer,
      ));
   }

   public function renderMenu(?string $activeRoute = null): Response
   {
      $g = $this->adminAccess->exigirUsuarioOlogin($this->getUser());
      if ($g instanceof RedirectResponse) {
         return $g;
      }
      $usuario = $g;
      $menu = $this->defaultService->DevolverMenu($usuario->getUsuarioId());

      return $this->render('admin/layout/menu.html.twig', [
         'usuario' => $usuario,
         'menu' => $menu,
         'activeRoute' => $activeRoute,
      ]);
   }

   public function renderModalItemProject()
   {
      $g = $this->adminAccess->exigirUsuarioOlogin($this->getUser());
      if ($g instanceof RedirectResponse) {
         return $g;
      }
      $usuario = $g;

      // items
      $items = $this->defaultService->getDoctrine()->getRepository(Item::class)
         ->ListarOrdenados();

      $equations = $this->defaultService->getDoctrine()->getRepository(Equation::class)
         ->ListarOrdenados();

      $units = $this->defaultService->getDoctrine()->getRepository(Unit::class)
         ->ListarOrdenados();

      $yields_calculation = $this->defaultService->ListarYieldsCalculation();

      return $this->render('admin/block/modal-item-project.html.twig', array(
         'items' => $items,
         'equations' => $equations,
         'yields_calculation' => $yields_calculation,
         'units' => $units,
         'usuario_bond' => $usuario->getBond() ? true : false,
         'usuario_retainage' => $usuario->getRetainage() ? true : false
      ));
   }

   public function renderModalInspector()
   {
      return $this->render('admin/block/modal-inspector.html.twig', array());
   }

   public function renderModalEquation()
   {
      return $this->render('admin/block/modal-equation.html.twig', array());
   }

   public function renderModalUnit()
   {
      return $this->render('admin/block/modal-unit.html.twig', array());
   }

   public function renderModalEstimateNoteItem()
   {
      return $this->render('admin/block/modal-estimate-note-item.html.twig', array());
   }

   public function renderModalEmployee()
   {
      return $this->render('admin/block/modal-employee.html.twig', array());
   }

   public function renderModalEmployeeSubcontractor()
   {
      return $this->render('admin/block/modal-employee-subcontractor.html.twig', array());
   }

   public function renderModalItemSubcontract()
   {

      $equations = $this->defaultService->getDoctrine()->getRepository(Equation::class)
         ->ListarOrdenados();

      $units = $this->defaultService->getDoctrine()->getRepository(Unit::class)
         ->ListarOrdenados();

      $yields_calculation = $this->defaultService->ListarYieldsCalculation();

      return $this->render('admin/block/modal-item-subcontract.html.twig', array(
         'equations' => $equations,
         'yields_calculation' => $yields_calculation,
         'units' => $units
      ));
   }

   public function renderModalAdvertisement()
   {

      $advertisements = $this->advertisementService->ListarAdvertisementsUltimosDias();

      return $this->render('admin/block/modal-advertisement.html.twig', array(
         'advertisement' => !empty($advertisements) ? $advertisements[0] : null,
      ));
   }

   public function renderModalConcreteVendor()
   {
      return $this->render('admin/block/modal-concrete-vendor.html.twig', array());
   }

   public function renderModalConcreteClass()
   {
      return $this->render('admin/block/modal-concrete-class.html.twig', array());
   }

   public function renderModalReminder()
   {
      return $this->render('admin/block/modal-reminder.html.twig', array());
   }

   public function renderModalCompany()
   {
      return $this->render('admin/block/modal-company.html.twig', array());
   }

   public function renderModalContactCompany()
   {
      return $this->render('admin/block/modal-contact-company.html.twig', array());
   }

   public function renderModalInvoice()
   {

      // companies
      $companies = $this->defaultService->getDoctrine()->getRepository(Company::class)
         ->ListarOrdenados();

      return $this->render('admin/block/modal-invoice.html.twig', array(
         'companies' => $companies,
      ));
   }
}
