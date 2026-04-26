<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Entity\Equation;
use App\Entity\Item;
use App\Entity\Unit;
use App\Repository\UserWidgetPreferenceRepository;
use App\Utils\Admin\AdvertisementService;
use App\Utils\Admin\DefaultService;
use App\Utils\Admin\EstimateService;
use App\Utils\Admin\DataTrackingService;
use App\Utils\Admin\LogService;
use App\Utils\Admin\NotificationService;
use App\Utils\Admin\ScheduleService;
use App\Utils\Admin\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
   private $defaultService;
   private $logService;
   private $notificationService;
   private $advertisementService;
   private TaskService $taskService;
   private ScheduleService $scheduleService;
   private EstimateService $estimateService;
   private DataTrackingService $dataTrackingService;
   private UserWidgetPreferenceRepository $widgetPrefRepo;

   public function __construct(
      DefaultService                  $defaultService,
      LogService                      $logService,
      NotificationService             $notificationService,
      AdvertisementService            $advertisementService,
      TaskService                     $taskService,
      ScheduleService                 $scheduleService,
      EstimateService                 $estimateService,
      DataTrackingService             $dataTrackingService,
      UserWidgetPreferenceRepository  $widgetPrefRepo
   ) {
      $this->defaultService = $defaultService;
      $this->logService = $logService;
      $this->notificationService = $notificationService;
      $this->advertisementService = $advertisementService;
      $this->taskService = $taskService;
      $this->scheduleService = $scheduleService;
      $this->estimateService = $estimateService;
      $this->dataTrackingService = $dataTrackingService;
      $this->widgetPrefRepo = $widgetPrefRepo;
   }

   public function index()
   {
      $usuario = $this->getUser();
      $permiso = $this->defaultService->BuscarPermiso($usuario->getUsuarioId(), 1);
      if (count($permiso) > 0 && $permiso[0]['ver']) {
         $dashboardWidgets = $this->defaultService->ObtenerWidgetsDashboardV3(
            $usuario->getUsuarioId(),
            $this->widgetPrefRepo
         );

         $homeTask = null;
         $homeWorkSchedule = null;
         $homeBidDeadlines = null;
         $homeEstimateWinLoss = null;
         $homeEstimatesSubmittedTotals = null;
         $homeEstimatorSubmittedShare = null;
         $homeCurrentMonthDataTracking = null;
         $homePayItemTotals = null;
         $homeInvoicedProjects = null;
         $homeInvoiceProfit = null;
         $homeCostBreakdown = null;
         foreach ($dashboardWidgets as $w) {
            if (!empty($w['id']) && $w['id'] === 'tasks') {
               $p40 = $this->taskService->BuscarPermiso($usuario->getUsuarioId(), 40);
               if (is_array($p40) && count($p40) > 0 && !empty($p40[0]['ver'])) {
                  $r0 = $this->taskService->resolverRangoFechasPeriodo('current_month', '', '');
                  $homeTask = [
                     'permiso' => $p40[0],
                     'tasks' => $this->taskService->listarTareasPayloadHome($usuario, $p40[0], $r0['inicial'], $r0['final']),
                     'range' => $r0,
                  ];
               }
               continue;
            }
            if (!empty($w['id']) && $w['id'] === 'work_schedule') {
               $r0 = $this->taskService->resolverRangoFechasPeriodo('current_month', '', '');
               $homeWorkSchedule = $this->scheduleService->listarSchedulesPayloadHome(
                  $r0['inicial'],
                  $r0['final']
               );
               continue;
            }
            if (!empty($w['id']) && $w['id'] === 'bid_deadlines') {
               $r0 = $this->taskService->resolverRangoFechasPeriodo('current_month', '', '');
               $homeBidDeadlines = $this->estimateService->listarUpcomingBidDeadlinesPayloadHome(
                  $r0['inicial'],
                  $r0['final']
               );
               continue;
            }
            if (!empty($w['id']) && $w['id'] === 'estimate_win_loss') {
               $r0 = $this->taskService->resolverRangoFechasPeriodo('current_month', '', '');
               $homeEstimateWinLoss = $this->defaultService->DevolverDataChartEstimateWinLoss(
                  '',
                  $r0['inicial'],
                  $r0['final']
               );
               continue;
            }
            if (!empty($w['id']) && $w['id'] === 'estimates_submitted_totals') {
               $r0 = $this->taskService->resolverRangoFechasPeriodo('current_month', '', '');
               $homeEstimatesSubmittedTotals = $this->defaultService->DevolverDataChartEstimateSubmittedTotals(
                  '',
                  $r0['inicial'],
                  $r0['final']
               );
               continue;
            }
            if (!empty($w['id']) && $w['id'] === 'estimator_submitted_share') {
               $r0 = $this->taskService->resolverRangoFechasPeriodo('current_month', '', '');
               $homeEstimatorSubmittedShare = $this->defaultService->DevolverDataChartEstimatorSubmittedShare(
                  '',
                  $r0['inicial'],
                  $r0['final']
               );
               continue;
            }
            if (!empty($w['id']) && $w['id'] === 'current_month_data_tracking') {
               $r0 = $this->taskService->resolverRangoFechasPeriodo('current_month', '', '');
               $homeCurrentMonthDataTracking = $this->dataTrackingService->listarCurrentMonthProjectsPayloadHome(
                  '',
                  $r0['inicial'],
                  $r0['final']
               );
               continue;
            }
            if (!empty($w['id']) && $w['id'] === 'pay_item_totals') {
               $r0 = $this->taskService->resolverRangoFechasPeriodo('current_month', '', '');
               $homePayItemTotals = $this->defaultService->ListarItemsConMontos(
                  '',
                  $r0['inicial'],
                  $r0['final'],
                  ''
               );
               continue;
            }
            if (!empty($w['id']) && $w['id'] === 'invoiced_projects') {
               $r0 = $this->taskService->resolverRangoFechasPeriodo('current_month', '', '');
               $homeInvoicedProjects = $this->defaultService->ListarInvoicedProjectsPayloadHome(
                  '',
                  $r0['inicial'],
                  $r0['final']
               );
               continue;
            }
            if (!empty($w['id']) && $w['id'] === 'invoice_profit_share') {
               $r0 = $this->taskService->resolverRangoFechasPeriodo('current_month', '', '');
               $homeInvoiceProfit = $this->defaultService->DevolverDataChartProfit(
                  '',
                  $r0['inicial'],
                  $r0['final']
               );
               continue;
            }
            if (!empty($w['id']) && $w['id'] === 'job_cost_breakdown') {
               $r0 = $this->taskService->resolverRangoFechasPeriodo('current_month', '', '');
               $homeCostBreakdown = $this->defaultService->DevolverDataChartCosts(
                  '',
                  $r0['inicial'],
                  $r0['final']
               );
            }
         }

         return $this->render('admin/default/index.html.twig', [
            'usuario' => $usuario,
            'dashboard_widgets' => $dashboardWidgets,
            'home_task' => $homeTask,
            'home_work_schedule' => $homeWorkSchedule,
            'home_bid_deadlines' => $homeBidDeadlines,
            'home_estimate_win_loss' => $homeEstimateWinLoss,
            'home_estimates_submitted_totals' => $homeEstimatesSubmittedTotals,
            'home_estimator_submitted_share' => $homeEstimatorSubmittedShare,
            'home_current_month_data_tracking' => $homeCurrentMonthDataTracking,
            'home_pay_item_totals' => $homePayItemTotals,
            'home_invoiced_projects' => $homeInvoicedProjects,
            'home_invoice_profit' => $homeInvoiceProfit,
            'home_cost_breakdown' => $homeCostBreakdown,
         ]);
      }

      return $this->redirectToRoute('denegado');
   }

   public function widgetPreferences(): Response
   {
      $usuario = $this->getUser();
      $allWidgets = $this->defaultService->ObtenerWidgetsDashboardV3($usuario->getUsuarioId());
      $prefMap   = $this->widgetPrefRepo->getPreferenceMapForUser($usuario->getUsuarioId());

      $widgets = array_map(static function (array $w) use ($prefMap): array {
         $w['user_active'] = $prefMap[$w['id']] ?? true;
         return $w;
      }, $allWidgets);

      return $this->render('admin/default/widget_preferences.html.twig', [
         'usuario' => $usuario,
         'widgets' => $widgets,
         'urlSave' => $this->generateUrl('saveUserWidgetPreference'),
      ]);
   }

   public function saveWidgetPreference(Request $request): \Symfony\Component\HttpFoundation\JsonResponse
   {
      $usuario   = $this->getUser();
      $widgetId  = $request->request->get('widget_id', '');
      $isActive  = filter_var($request->request->get('is_active', true), FILTER_VALIDATE_BOOLEAN);

      if ($widgetId === '') {
         return $this->json(['success' => false, 'message' => 'Missing widget_id'], 400);
      }

      $this->widgetPrefRepo->savePreference($usuario->getUsuarioId(), $widgetId, $isActive);

      return $this->json(['success' => true]);
   }

   /**
    * listarStats Acción para filtrar el dashboard
    *
    */
   public function listarStats(Request $request)
   {
      $usuario = $this->getUser();
      if ($usuario === null || !method_exists($usuario, 'getUsuarioId')) {
         return $this->json([
            'success' => false,
            'error' => 'Unauthenticated',
         ], 401);
      }

      $project_id = $request->get('project_id');
      $status = $request->get('status');
      $fecha_inicial = $request->get('fechaInicial');
      $fecha_fin = $request->get('fechaFin');

      try {


         $stats = $this->defaultService->FiltrarDashboard($project_id, $status, $fecha_inicial, $fecha_fin);
         $p40 = $this->taskService->BuscarPermiso($usuario->getUsuarioId(), 40);
         if (is_array($p40) && count($p40) > 0 && !empty($p40[0]['ver'])) {
            $stats['tasks'] = $this->taskService->listarTareasPayloadHome(
               $usuario,
               $p40[0],
               (string) $fecha_inicial,
               (string) $fecha_fin
            );
         }
         $p22 = $this->scheduleService->BuscarPermiso($usuario->getUsuarioId(), 22);
         if (is_array($p22) && count($p22) > 0 && !empty($p22[0]['ver'])) {
            $stats['work_schedule'] = $this->scheduleService->listarSchedulesPayloadHome(
               (string) $fecha_inicial,
               (string) $fecha_fin,
               30,
               (string) $project_id
            );
         }
         $p29 = $this->estimateService->BuscarPermiso($usuario->getUsuarioId(), 29);
         if (is_array($p29) && count($p29) > 0 && !empty($p29[0]['ver'])) {
            $stats['bid_deadlines'] = $this->estimateService->listarUpcomingBidDeadlinesPayloadHome(
               (string) $fecha_inicial,
               (string) $fecha_fin,
               0,
               ''
            );
         }
         $p10 = $this->dataTrackingService->BuscarPermiso($usuario->getUsuarioId(), 10);
         if (is_array($p10) && count($p10) > 0 && !empty($p10[0]['ver'])) {
            $stats['current_month_data_tracking'] = $this->dataTrackingService->listarCurrentMonthProjectsPayloadHome(
               (string) $project_id,
               (string) $fecha_inicial,
               (string) $fecha_fin
            );
            $stats['pay_item_totals'] = $this->defaultService->ListarItemsConMontos(
               (string) $project_id,
               (string) $fecha_inicial,
               (string) $fecha_fin,
               ''
            );
         }

         $resultadoJson['success'] = true;
         $resultadoJson['stats'] = $stats;

         return $this->json($resultadoJson);
      } catch (\Exception $e) {
         $resultadoJson['success'] = false;
         $resultadoJson['error'] = $e->getMessage();

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
      $usuario = $this->getUser();
      $menu = $this->defaultService->DevolverMenu($usuario->getUsuarioId());

      return $this->render('admin/layout/menu.html.twig', [
         'usuario' => $usuario,
         'menu' => $menu,
         'activeRoute' => $activeRoute,
      ]);
   }

   public function renderModalItemProject()
   {
      $usuario = $this->getUser();

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
