<?php

namespace App\Controller\Admin;

use App\Entity\Equation;
use App\Entity\Item;
use App\Entity\Unit;
use App\Utils\Admin\AdvertisementService;
use App\Utils\Admin\DefaultService;
use App\Utils\Admin\LogService;
use App\Utils\Admin\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    private $defaultService;
    private $logService;
    private $notificationService;

    private $advertisementService;

    public function __construct(DefaultService       $defaultService, LogService $logService,
                                NotificationService  $notificationService,
                                AdvertisementService $advertisementService)
    {
        $this->defaultService = $defaultService;
        $this->logService = $logService;
        $this->notificationService = $notificationService;
        $this->advertisementService = $advertisementService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->defaultService->BuscarPermiso($usuario->getUsuarioId(), 1);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                // primer dia del mes
                $from = $this->defaultService->ObtenerPrimerDiaMes();

                //last 6 projects
                $projects = $this->defaultService->ListarProjectsParaDashboard('', $from, '', 'DESC', 6);

                // filter projects
                $filter_projects = $this->defaultService->ListarProjectsParaDashboard('', $from);

                // stats
                $stats = $this->defaultService->ListarStats($from);
                // chart 1
                $chart1 = $this->defaultService->DevolverDataChartCosts('', $from);
                // chart 2
                $chart2 = $this->defaultService->DevolverDataChartProfit('', $from);
                // chart 3
                $chart3 = $this->defaultService->DevolverDataChart3('', $from);
                // items
                $items = $this->defaultService->ListarItemsConMontos('', $from);
                // materials
                $materials = $this->defaultService->ListarMaterialsConMontos('', $from);

                return $this->render('admin/default/index.html.twig', array(
                    'usuario' => $usuario,
                    'stats' => $stats,
                    'chart1' => $chart1,
                    'chart2' => $chart2,
                    'chart3' => $chart3,
                    'items' => $items,
                    'materials' => $materials,
                    'projects' => $projects,
                    'filter_projects' => $filter_projects,
                ));
            }
        } else {
            return $this->redirectToRoute('denegado');
        }
    }

    /**
     * listarStats Acción para filtrar el dashboard
     *
     */
    public function listarStats(Request $request)
    {
        $project_id = $request->get('project_id');
        $status = $request->get('status');
        $fecha_inicial = $request->get('fechaInicial');
        $fecha_fin = $request->get('fechaFin');

        try {


            $stats = $this->defaultService->FiltrarDashboard($project_id, $status, $fecha_inicial, $fecha_fin);

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

    public function renderMenu()
    {
        $usuario = $this->getUser();

        $menu = $this->defaultService->DevolverMenuIcan($usuario->getUsuarioId());

        return $this->render('admin/layout/menu.html.twig', array(
            'usuario' => $usuario,
            'menu' => $menu
        ));
    }

    public function renderModalItemProject()
    {

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
            'units' => $units
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
}
