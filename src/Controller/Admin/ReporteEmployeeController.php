<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\ReporteEmployee\ReporteEmployeeExportFiltroRequest;
use App\Dto\Admin\ReporteEmployee\ReporteEmployeeListarRequest;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\EmployeeService;
use App\Service\Admin\ProjectService;
use App\Service\Admin\ReporteEmployeeService;
use Symfony\Component\HttpFoundation\JsonResponse;

class ReporteEmployeeController extends AbstractAdminController
{
    private $reporteService;
    private $projectService;
    private $employeeService;

    public function __construct(
        AdminAccessService $adminAccess,
        ReporteEmployeeService $reporteService,
        ProjectService $projectService,
        EmployeeService $employeeService)
    {
        parent::__construct($adminAccess);
        $this->reporteService = $reporteService;
        $this->projectService = $projectService;
        $this->employeeService = $employeeService;
    }

    #[RequireAdminPermission(FunctionId::REPORTE_EMPLOYEE)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::REPORTE_EMPLOYEE);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso REPORTE_EMPLOYEE esperado tras #[RequireAdminPermission].');

        // employees
        $employees = $this->employeeService->ListarOrdenados();

        // projects
        $projects = $this->projectService->ListarOrdenados();

        return $this->render('admin/reportes/employee.html.twig', [
            'permiso' => $permiso,
            'employees' => $employees,
            'projects' => $projects,
        ]);
    }

    /**
     * listar Acción que lista los companies.
     */
    #[RequireAdminPermission(FunctionId::REPORTE_EMPLOYEE, AdminPermission::View, jsonOnDenied: true)]
    public function listar(ReporteEmployeeListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $employee_id = $listar->employee_id;
            $project_id = $listar->project_id;
            $fecha_inicial = $listar->fechaInicial;
            $fecha_fin = $listar->fechaFin;

            // total + data en una sola llamada a tu servicio
            $result = $this->reporteService->ListarReporteEmployees(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $employee_id,
                $project_id,
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
    #[RequireAdminPermission(FunctionId::REPORTE_EMPLOYEE, AdminPermission::View, jsonOnDenied: true)]
    public function exportarExcel(ReporteEmployeeExportFiltroRequest $f): JsonResponse
    {
        $search = $f->search;
        $employee_id = $f->employee_id;
        $project_id = $f->project_id;
        $fecha_inicial = $f->fecha_inicial;
        $fecha_fin = $f->fecha_fin;

        try {
            $url = $this->reporteService->ExportarExcel($search, $employee_id, $project_id, $fecha_inicial, $fecha_fin);

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
    #[RequireAdminPermission(FunctionId::REPORTE_EMPLOYEE, AdminPermission::View, jsonOnDenied: true)]
    public function devolverTotal(ReporteEmployeeExportFiltroRequest $f): JsonResponse
    {
        $search = $f->search;
        $employee_id = $f->employee_id;
        $project_id = $f->project_id;
        $fecha_inicial = $f->fecha_inicial;
        $fecha_fin = $f->fecha_fin;

        try {
            $total = $this->reporteService->DevolverTotal($search, $employee_id, $project_id, $fecha_inicial, $fecha_fin);

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
