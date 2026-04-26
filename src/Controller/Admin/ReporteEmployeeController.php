<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;

use App\Http\DataTablesHelper;
use App\Utils\Admin\ProjectService;
use App\Utils\Admin\ReporteEmployeeService;

use App\Utils\Admin\EmployeeService;
use App\Service\Admin\AdminAccessService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ReporteEmployeeController extends AbstractAdminController
{

    private $reporteService;
    private $projectService;
    private $employeeService;

    public function __construct(AdminAccessService $adminAccess, ReporteEmployeeService $reporteService, ProjectService $projectService, EmployeeService $employeeService)
    {
        parent::__construct($adminAccess);
        $this->reporteService = $reporteService;
        $this->projectService = $projectService;
        $this->employeeService = $employeeService;
    }

    public function index()
    {
        $acceso = $this->adminAccess->exigirUsuarioYPermisoVer($this->getUser(), FunctionId::REPORTE_EMPLOYEE);
        if ($acceso instanceof RedirectResponse) {
            return $acceso;
        }
        $permiso = $acceso['permisos'];

        // employees
        $employees = $this->employeeService->ListarOrdenados();

        // projects
        $projects = $this->projectService->ListarOrdenados();

        return $this->render('admin/reportes/employee.html.twig', array(
            'permiso' => $permiso[0],
            'employees' => $employees,
            'projects' => $projects,
        ));
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

            $employee_id = $request->get('employee_id');
            $project_id = $request->get('project_id');
            $fecha_inicial = $request->get('fechaInicial');
            $fecha_fin = $request->get('fechaFin');

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
        $employee_id = $request->get('employee_id');
        $project_id = $request->get('project_id');
        $fecha_inicial = $request->get('fecha_inicial');
        $fecha_fin = $request->get('fecha_fin');

        try {
            $url = $this->reporteService->ExportarExcel($search, $employee_id, $project_id, $fecha_inicial, $fecha_fin);

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
        $employee_id = $request->get('employee_id');
        $project_id = $request->get('project_id');
        $fecha_inicial = $request->get('fecha_inicial');
        $fecha_fin = $request->get('fecha_fin');

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
