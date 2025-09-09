<?php

namespace App\Controller\Admin;


use App\Entity\Project;
use App\Entity\Subcontractor;

use App\Http\DataTablesHelper;
use App\Utils\Admin\ProjectService;
use App\Utils\Admin\ReporteSubcontractorService;

use App\Utils\Admin\SubcontractorService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReporteSubcontractorController extends AbstractController
{

    private $reporteService;
    private $projectService;
    private $subcontractorService;

    public function __construct(ReporteSubcontractorService $reporteService, ProjectService $projectService, SubcontractorService $subcontractorService)
    {
        $this->reporteService = $reporteService;
        $this->projectService = $projectService;
        $this->subcontractorService = $subcontractorService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->reporteService->BuscarPermiso($usuario->getUsuarioId(), 19);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                // subcontractors
                $subcontractors = $this->subcontractorService->ListarOrdenados();

                // projects
                $projects = $this->projectService->ListarOrdenados();

                return $this->render('admin/reportes/subcontractor.html.twig', array(
                    'permiso' => $permiso[0],
                    'subcontractors' => $subcontractors,
                    'projects' => $projects,
                ));
            }
        } else {
            return $this->redirectToRoute('denegado');
        }
    }

    /**
     * listar Acción que lista los companies
     *
     */
    public function listar(Request $request)
    {
        try {
            // parsear los parametros de la tabla
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: ['id', 'date', 'project', 'subcontractor', 'item', 'unit', 'quantity', 'price', 'total'],
                defaultOrderField: 'date'
            );

            $subcontractor_id = $request->get('subcontractor_id');
            $project_id = $request->get('project_id');
            $project_item_id = $request->get('project_item_id');
            $fecha_inicial = $request->get('fechaInicial');
            $fecha_fin = $request->get('fechaFin');

            // total + data en una sola llamada a tu servicio
            $result = $this->reporteService->ListarReporteSubcontractors(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $subcontractor_id,
                $project_id,
                $project_item_id,
                $fecha_inicial,
                $fecha_fin
            );

            $resultadoJson = [
                'draw'            => $dt['draw'],
                'data'            => $result['data'],
                'recordsTotal'    => (int) $result['total'],
                'recordsFiltered' => (int) $result['total'],
            ];

            return $this->json($resultadoJson);

        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * exportarExcel Acción para la exportacion en excel
     *
     */
    public function exportarExcel(Request $request)
    {

        $search = $request->get('search');
        $subcontractor_id = $request->get('subcontractor_id');
        $project_id = $request->get('project_id');
        $project_item_id = $request->get('project_item_id');
        $fecha_inicial = $request->get('fecha_inicial');
        $fecha_fin = $request->get('fecha_fin');

        try {
            $url = $this->reporteService->ExportarExcel($search, $subcontractor_id, $project_id, $project_item_id, $fecha_inicial, $fecha_fin);

            $resultadoJson['success'] = true;
            $resultadoJson['message'] = "The operation was successful";
            $resultadoJson['url'] = $url;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * devolverTotal Acción para devolver el total
     *
     */
    public function devolverTotal(Request $request)
    {

        $search = $request->get('search');
        $subcontractor_id = $request->get('subcontractor_id');
        $project_id = $request->get('project_id');
        $project_item_id = $request->get('project_item_id');
        $fecha_inicial = $request->get('fecha_inicial');
        $fecha_fin = $request->get('fecha_fin');

        try {
            $total = $this->reporteService->DevolverTotal($search, $subcontractor_id, $project_id, $project_item_id, $fecha_inicial, $fecha_fin);

            $resultadoJson['success'] = true;
            $resultadoJson['total'] = $total;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }
}
