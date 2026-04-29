<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Default\DashboardListarStatsRequest;
use App\Dto\Admin\Default\SaveWidgetPreferenceRequest;
use App\Entity\Company;
use App\Entity\Equation;
use App\Entity\Item;
use App\Entity\Unit;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\AdvertisementService;
use App\Service\Admin\DataTrackingService;
use App\Service\Admin\DefaultService;
use App\Service\Admin\EstimateService;
use App\Service\Admin\LogService;
use App\Service\Admin\NotificationService;
use App\Service\Admin\ScheduleService;
use App\Service\Admin\TaskService;
use App\Service\Admin\WidgetAccessService;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        DefaultService $defaultService,
        LogService $logService,
        NotificationService $notificationService,
        AdvertisementService $advertisementService,
        TaskService $taskService,
        ScheduleService $scheduleService,
        EstimateService $estimateService,
        DataTrackingService $dataTrackingService,
        WidgetAccessService $widgetAccessService)
    {
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

    #[RequireAdminPermission(FunctionId::HOME)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();

        $dashboardWidgets = $this->defaultService->ObtenerWidgetsDashboardV3(
            $usuario->getUsuarioId()
        );
        $homePayloads = $this->defaultService->construirPayloadsWidgetsHome($usuario, $dashboardWidgets);

        return $this->render('admin/default/index.html.twig', \array_merge([
            'usuario' => $usuario,
            'dashboard_widgets' => $dashboardWidgets,
        ], $homePayloads));
    }

    #[RequireAdminPermission(FunctionId::HOME)]
    public function widgetPreferences(): Response
    {
        $usuario = $this->DevolverUsuario();
        $widgets = $this->defaultService->ObtenerMyWidgetsTogglesV3($usuario->getUsuarioId());

        return $this->render('admin/default/widget_preferences.html.twig', [
            'usuario' => $usuario,
            'widgets' => $widgets,
            'urlSave' => $this->generateUrl('saveUserWidgetPreference'),
        ]);
    }

    #[RequireAdminPermission(FunctionId::HOME, AdminPermission::Edit, jsonOnDenied: true)]
    public function saveWidgetPreference(SaveWidgetPreferenceRequest $dto): JsonResponse
    {
        $usuario = $this->DevolverUsuario();

        try {
            $this->widgetAccessService->setUserWidgetFromMyWidgetsPageFromDto(
                $usuario->getUsuarioId(),
                $dto
            );
        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return $this->json(['success' => true]);
    }

    /**
     * listarStats Acción para filtrar el dashboard.
     */
    #[RequireAdminPermission(FunctionId::HOME, AdminPermission::View, jsonOnDenied: true)]
    public function listarStats(DashboardListarStatsRequest $f): JsonResponse
    {
        $usuario = $this->DevolverUsuario();
        $userId = $usuario->getUsuarioId();
        $w = $this->widgetAccessService;
        $projectId = (string) ($f->project_id ?? '');
        $status = (string) ($f->status ?? '');
        $fechaInicial = (string) ($f->fechaInicial ?? '');
        $fechaFin = (string) ($f->fechaFin ?? '');

        try {
            $stats = [];

            if ($w->isWidgetVisibleOnHome($userId, 'tasks')) {
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

            if ($w->isWidgetVisibleOnHome($userId, 'work_schedule')) {
                $stats['work_schedule'] = $this->scheduleService->listarSchedulesPayloadHome(
                    $fechaInicial,
                    $fechaFin,
                    30,
                    $projectId
                );
            }

            if ($w->isWidgetVisibleOnHome($userId, 'bid_deadlines')) {
                $stats['bid_deadlines'] = $this->estimateService->listarUpcomingBidDeadlinesPayloadHome(
                    $fechaInicial,
                    $fechaFin,
                    0,
                    ''
                );
            }

            if ($w->isWidgetVisibleOnHome($userId, 'current_month_data_tracking')) {
                $stats['current_month_data_tracking'] = $this->dataTrackingService->listarCurrentMonthProjectsPayloadHome(
                    $projectId,
                    $fechaInicial,
                    $fechaFin
                );
            }

            if ($w->isWidgetVisibleOnHome($userId, 'pay_item_totals')) {
                $stats['pay_item_totals'] = $this->defaultService->ListarItemsConMontos(
                    $projectId,
                    $fechaInicial,
                    $fechaFin,
                    $status
                );
            }

            if ($w->isWidgetVisibleOnHome($userId, 'invoiced_projects')) {
                $stats['invoiced_projects'] = $this->defaultService->ListarInvoicedProjectsPayloadHome(
                    $projectId,
                    $fechaInicial,
                    $fechaFin
                );
            }

            if ($w->isWidgetVisibleOnHome($userId, 'estimate_win_loss')) {
                $stats['chart_estimate_win_loss'] = $this->defaultService->DevolverDataChartEstimateWinLoss(
                    '',
                    $fechaInicial,
                    $fechaFin
                );
            }
            if ($w->isWidgetVisibleOnHome($userId, 'estimates_submitted_totals')) {
                $stats['chart_estimates_submitted_totals'] = $this->defaultService->DevolverDataChartEstimateSubmittedTotals(
                    '',
                    $fechaInicial,
                    $fechaFin
                );
            }
            if ($w->isWidgetVisibleOnHome($userId, 'estimator_submitted_share')) {
                $stats['chart_estimator_submitted_share'] = $this->defaultService->DevolverDataChartEstimatorSubmittedShare(
                    '',
                    $fechaInicial,
                    $fechaFin
                );
            }
            if ($w->isWidgetVisibleOnHome($userId, 'invoice_profit_share')) {
                $stats['chart_profit'] = $this->defaultService->DevolverDataChartProfit(
                    $projectId,
                    $fechaInicial,
                    $fechaFin,
                    $status
                );
            }
            if ($w->isWidgetVisibleOnHome($userId, 'job_cost_breakdown')) {
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
        $sin_leer = 0;
        if (null != $usuario) {
            $logs = $this->logService->ListarLogsUltimosDias($usuario);

            $notificaciones = $this->notificationService->ListarNotificationsUltimosDias($usuario);

            // contar sin leer
            foreach ($notificaciones as $value) {
                if (!$value['leida']) {
                    ++$sin_leer;
                }
            }
        }

        $advertisements = $this->advertisementService->ListarAdvertisementsUltimosDias();

        return $this->render('admin/layout/header.html.twig', [
            'usuario' => $usuario,
            'logs' => $logs,
            'notificaciones' => $notificaciones,
            'advertisements' => $advertisements,
            'notificaciones_sin_leer' => $sin_leer,
        ]);
    }

    #[RequireAdminPermission(FunctionId::HOME)]
    public function renderMenu(?string $activeRoute = null): Response
    {
        $usuario = $this->DevolverUsuario();
        $menu = $this->defaultService->DevolverMenu($usuario->getUsuarioId());

        return $this->render('admin/layout/menu.html.twig', [
            'usuario' => $usuario,
            'menu' => $menu,
            'activeRoute' => $activeRoute,
        ]);
    }

    #[RequireAdminPermission(FunctionId::HOME)]
    public function renderModalItemProject()
    {
        $usuario = $this->DevolverUsuario();

        // items
        $items = $this->defaultService->getDoctrine()->getRepository(Item::class)
           ->ListarOrdenados();

        $equations = $this->defaultService->getDoctrine()->getRepository(Equation::class)
           ->ListarOrdenados();

        $units = $this->defaultService->getDoctrine()->getRepository(Unit::class)
           ->ListarOrdenados();

        $yields_calculation = $this->defaultService->ListarYieldsCalculation();

        return $this->render('admin/block/modal-item-project.html.twig', [
            'items' => $items,
            'equations' => $equations,
            'yields_calculation' => $yields_calculation,
            'units' => $units,
            'usuario_bond' => $usuario->getBond() ? true : false,
            'usuario_retainage' => $usuario->getRetainage() ? true : false,
        ]);
    }

    public function renderModalInspector()
    {
        return $this->render('admin/block/modal-inspector.html.twig', []);
    }

    public function renderModalEquation()
    {
        return $this->render('admin/block/modal-equation.html.twig', []);
    }

    public function renderModalUnit()
    {
        return $this->render('admin/block/modal-unit.html.twig', []);
    }

    public function renderModalEstimateNoteItem()
    {
        return $this->render('admin/block/modal-estimate-note-item.html.twig', []);
    }

    public function renderModalEmployee()
    {
        return $this->render('admin/block/modal-employee.html.twig', []);
    }

    public function renderModalEmployeeSubcontractor()
    {
        return $this->render('admin/block/modal-employee-subcontractor.html.twig', []);
    }

    public function renderModalItemSubcontract()
    {
        $equations = $this->defaultService->getDoctrine()->getRepository(Equation::class)
           ->ListarOrdenados();

        $units = $this->defaultService->getDoctrine()->getRepository(Unit::class)
           ->ListarOrdenados();

        $yields_calculation = $this->defaultService->ListarYieldsCalculation();

        return $this->render('admin/block/modal-item-subcontract.html.twig', [
            'equations' => $equations,
            'yields_calculation' => $yields_calculation,
            'units' => $units,
        ]);
    }

    public function renderModalAdvertisement()
    {
        $advertisements = $this->advertisementService->ListarAdvertisementsUltimosDias();

        return $this->render('admin/block/modal-advertisement.html.twig', [
            'advertisement' => !empty($advertisements) ? $advertisements[0] : null,
        ]);
    }

    public function renderModalConcreteVendor()
    {
        return $this->render('admin/block/modal-concrete-vendor.html.twig', []);
    }

    public function renderModalConcreteClass()
    {
        return $this->render('admin/block/modal-concrete-class.html.twig', []);
    }

    public function renderModalReminder()
    {
        return $this->render('admin/block/modal-reminder.html.twig', []);
    }

    public function renderModalCompany()
    {
        return $this->render('admin/block/modal-company.html.twig', []);
    }

    public function renderModalContactCompany()
    {
        return $this->render('admin/block/modal-contact-company.html.twig', []);
    }

    public function renderModalInvoice()
    {
        // companies
        $companies = $this->defaultService->getDoctrine()->getRepository(Company::class)
           ->ListarOrdenados();

        return $this->render('admin/block/modal-invoice.html.twig', [
            'companies' => $companies,
        ]);
    }
}
