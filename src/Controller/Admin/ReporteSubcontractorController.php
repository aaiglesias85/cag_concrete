<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Controller\Admin\Traits\AdminValidationResponseTrait;
use App\Dto\Admin\ReporteSubcontractor\ReporteSubcontractorExportFiltroRequest;
use App\Dto\Admin\ReporteSubcontractor\ReporteSubcontractorListarFiltroRequest;
use App\Http\DataTablesHelper;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\ProjectService;
use App\Service\Admin\ReporteSubcontractorService;
use App\Service\Admin\SubcontractorService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReporteSubcontractorController extends AbstractAdminController
{
    use AdminValidationResponseTrait;

    private $reporteService;
    private $projectService;
    private $subcontractorService;

    public function __construct(
        AdminAccessService $adminAccess,
        ReporteSubcontractorService $reporteService,
        ProjectService $projectService,
        SubcontractorService $subcontractorService,
        private ValidatorInterface $validator,
        private TranslatorInterface $adminTranslator,
    ) {
        parent::__construct($adminAccess);
        $this->reporteService = $reporteService;
        $this->projectService = $projectService;
        $this->subcontractorService = $subcontractorService;
    }

    public function index()
    {
        $acceso = $this->adminAccess->exigirUsuarioYPermisoVer($this->getUser(), FunctionId::REPORTE_SUBCONTRACTOR);
        if ($acceso instanceof RedirectResponse) {
            return $acceso;
        }
        $permiso = $acceso['permisos'];

        // subcontractors
        $subcontractors = $this->subcontractorService->ListarOrdenados();

        // projects
        $projects = $this->projectService->ListarOrdenados();

        return $this->render('admin/reportes/subcontractor.html.twig', [
            'permiso' => $permiso[0],
            'subcontractors' => $subcontractors,
            'projects' => $projects,
        ]);
    }

    /**
     * listar Acción que lista los companies.
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

            $f = ReporteSubcontractorListarFiltroRequest::fromHttpRequest($request);
            $this->validateAdminDto($this->validator, $f, $this->adminTranslator);
            $subcontractor_id = $f->subcontractor_id;
            $project_id = $f->project_id;
            $project_item_id = $f->project_item_id;
            $fecha_inicial = $f->fechaInicial;
            $fecha_fin = $f->fechaFin;

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
    public function exportarExcel(Request $request)
    {
        $f = ReporteSubcontractorExportFiltroRequest::fromHttpRequest($request);
        $this->validateAdminDto($this->validator, $f, $this->adminTranslator);
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
    public function devolverTotal(Request $request)
    {
        $f = ReporteSubcontractorExportFiltroRequest::fromHttpRequest($request);
        $this->validateAdminDto($this->validator, $f, $this->adminTranslator);
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
