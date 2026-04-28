<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\ReporteSubcontractor\ReporteSubcontractorExportFiltroRequest;
use App\Dto\Admin\ReporteSubcontractor\ReporteSubcontractorListarRequest;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\ProjectService;
use App\Service\Admin\ReporteSubcontractorService;
use App\Service\Admin\SubcontractorService;
use Symfony\Component\HttpFoundation\JsonResponse;
class ReporteSubcontractorController extends AbstractAdminController
{
    private $reporteService;
    private $projectService;
    private $subcontractorService;

    public function __construct(
        AdminAccessService $adminAccess,
        ReporteSubcontractorService $reporteService,
        ProjectService $projectService,
        SubcontractorService $subcontractorService) {
        parent::__construct($adminAccess);
        $this->reporteService = $reporteService;
        $this->projectService = $projectService;
        $this->subcontractorService = $subcontractorService;
    }

    #[RequireAdminPermission(FunctionId::REPORTE_SUBCONTRACTOR)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::REPORTE_SUBCONTRACTOR);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso REPORTE_SUBCONTRACTOR esperado tras #[RequireAdminPermission].');

        // subcontractors
        $subcontractors = $this->subcontractorService->ListarOrdenados();

        // projects
        $projects = $this->projectService->ListarOrdenados();

        return $this->render('admin/reportes/subcontractor.html.twig', [
            'permiso' => $permiso,
            'subcontractors' => $subcontractors,
            'projects' => $projects,
        ]);
    }

    /**
     * listar Acción que lista los companies.
     */
    #[RequireAdminPermission(FunctionId::REPORTE_SUBCONTRACTOR, AdminPermission::View, jsonOnDenied: true)]
    public function listar(ReporteSubcontractorListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $subcontractor_id = $listar->subcontractor_id;
            $project_id = $listar->project_id;
            $project_item_id = $listar->project_item_id;
            $fecha_inicial = $listar->fechaInicial;
            $fecha_fin = $listar->fechaFin;

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
                'draw' => $dt['draw'],
                'data' => $result['data'],
                'recordsTotal' => (int) $result['total'],
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
     * exportarExcel Acción para la exportacion en excel.
     */
    #[RequireAdminPermission(FunctionId::REPORTE_SUBCONTRACTOR, AdminPermission::View, jsonOnDenied: true)]
    public function exportarExcel(ReporteSubcontractorExportFiltroRequest $f): JsonResponse
    {
        $search = $f->search;
        $subcontractor_id = $f->subcontractor_id;
        $project_id = $f->project_id;
        $project_item_id = $f->project_item_id;
        $fecha_inicial = $f->fecha_inicial;
        $fecha_fin = $f->fecha_fin;

        try {
            $url = $this->reporteService->ExportarExcel($search, $subcontractor_id, $project_id, $project_item_id, $fecha_inicial, $fecha_fin);

            $resultadoJson['success'] = true;
            $resultadoJson['message'] = 'The operation was successful';
            $resultadoJson['url'] = $url;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * devolverTotal Acción para devolver el total.
     */
    #[RequireAdminPermission(FunctionId::REPORTE_SUBCONTRACTOR, AdminPermission::View, jsonOnDenied: true)]
    public function devolverTotal(ReporteSubcontractorExportFiltroRequest $f): JsonResponse
    {
        $search = $f->search;
        $subcontractor_id = $f->subcontractor_id;
        $project_id = $f->project_id;
        $project_item_id = $f->project_item_id;
        $fecha_inicial = $f->fecha_inicial;
        $fecha_fin = $f->fecha_fin;

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
