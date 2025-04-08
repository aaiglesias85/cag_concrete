<?php

namespace App\Controller\Admin;


use App\Entity\Project;
use App\Entity\Subcontractor;

use App\Utils\Admin\ReporteSubcontractorService;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReporteSubcontractorController extends AbstractController
{

    private $reporteService;

    public function __construct(ReporteSubcontractorService $reporteService)
    {
        $this->reporteService = $reporteService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->reporteService->BuscarPermiso($usuario->getUsuarioId(), 19);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                // subcontractors
                $subcontractors = $this->reporteService->getDoctrine()->getRepository(Subcontractor::class)
                    ->ListarOrdenados();

                return $this->render('admin/reportes/subcontractor.html.twig', array(
                    'permiso' => $permiso[0],
                    'subcontractors' => $subcontractors,
                ));
            }
        } else {
            return $this->redirectToRoute('denegado');
        }
    }

    /**
     * listar Acción que lista los projects
     *
     */
    public function listar(Request $request)
    {

        // search filter by keywords
        $query = !empty($request->get('query')) ? $request->get('query') : array();
        $sSearch = isset($query['generalSearch']) && is_string($query['generalSearch']) ? $query['generalSearch'] : '';
        $subcontractor_id = isset($query['subcontractor_id']) && is_string($query['subcontractor_id']) ? $query['subcontractor_id'] : '';
        $project_id = isset($query['project_id']) && is_string($query['project_id']) ? $query['project_id'] : '';
        $project_item_id = isset($query['project_item_id']) && is_string($query['project_item_id']) ? $query['project_item_id'] : '';
        $fecha_inicial = isset($query['fechaInicial']) && is_string($query['fechaInicial']) ? $query['fechaInicial'] : '';
        $fecha_fin = isset($query['fechaFin']) && is_string($query['fechaFin']) ? $query['fechaFin'] : '';

        //Sort
        $sort = !empty($request->get('sort')) ? $request->get('sort') : array();
        $sSortDir_0 = !empty($sort['sort']) ? $sort['sort'] : 'desc';
        $iSortCol_0 = !empty($sort['field']) ? $sort['field'] : 'date';
        //$start and $limit
        $pagination = !empty($request->get('pagination')) ? $request->get('pagination') : array();
        $page = !empty($pagination['page']) ? (int)$pagination['page'] : 1;
        $limit = !empty($pagination['perpage']) ? (int)$pagination['perpage'] : -1;
        $start = 0;

        try {
            $pages = 1;
            $total = $this->reporteService->TotalReporteSubcontractors($sSearch, $subcontractor_id, $project_id, $project_item_id, $fecha_inicial, $fecha_fin);
            if ($limit > 0) {
                $pages = ceil($total / $limit); // calculate total pages
                $page = max($page, 1); // get 1 page when $_REQUEST['page'] <= 0
                $page = min($page, $pages); // get last page when $_REQUEST['page'] > $totalPages
                $start = ($page - 1) * $limit;
                if ($start < 0) {
                    $start = 0;
                }
            }

            $meta = array(
                'page' => $page,
                'pages' => $pages,
                'perpage' => $limit,
                'total' => $total,
                'field' => $iSortCol_0,
                'sort' => $sSortDir_0
            );

            $data = $this->reporteService->ListarReporteSubcontractors($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0,
                $subcontractor_id, $project_id, $project_item_id, $fecha_inicial, $fecha_fin);

            $resultadoJson = array(
                'meta' => $meta,
                'data' => $data
            );

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
