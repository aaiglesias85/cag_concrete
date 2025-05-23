<?php

namespace App\Controller\Admin;

use App\Utils\Admin\OverheadPriceService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OverheadPriceController extends AbstractController
{

    private $overheadService;

    public function __construct(OverheadPriceService $overheadService)
    {
        $this->overheadService = $overheadService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->overheadService->BuscarPermiso($usuario->getUsuarioId(), 16);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                return $this->render('admin/overhead-price/index.html.twig', array(
                    'permiso' => $permiso[0]
                ));
            }
        } else {
            return $this->redirectToRoute('denegado');
        }
    }

    /**
     * listar Acción que lista los overheads
     *
     */
    public function listar(Request $request)
    {

        // search filter by keywords
        $query = !empty($request->get('query')) ? $request->get('query') : array();
        $sSearch = isset($query['generalSearch']) && is_string($query['generalSearch']) ? $query['generalSearch'] : '';
        //Sort
        $sort = !empty($request->get('sort')) ? $request->get('sort') : array();
        $sSortDir_0 = !empty($sort['sort']) ? $sort['sort'] : 'asc';
        $iSortCol_0 = !empty($sort['field']) ? $sort['field'] : 'name';
        //$start and $limit
        $pagination = !empty($request->get('pagination')) ? $request->get('pagination') : array();
        $page = !empty($pagination['page']) ? (int)$pagination['page'] : 1;
        $limit = !empty($pagination['perpage']) ? (int)$pagination['perpage'] : -1;
        $start = 0;

        try {
            $pages = 1;
            $total = $this->overheadService->TotalOverheads($sSearch);
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

            $data = $this->overheadService->ListarOverheads($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

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
     * salvar Acción que inserta un menu en la BD
     *
     */
    public function salvar(Request $request)
    {
        $overhead_id = $request->get('overhead_id');

        $name = $request->get('name');
        $price = $request->get('price');
        
        try {

            if ($overhead_id == "") {
                $resultado = $this->overheadService->SalvarOverhead($name, $price);
            } else {
                $resultado = $this->overheadService->ActualizarOverhead($overhead_id, $name, $price);
            }

            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";

                return $this->json($resultadoJson);
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];

                return $this->json($resultadoJson);
            }
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminar Acción que elimina un overhead en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $overhead_id = $request->get('overhead_id');

        try {
            $resultado = $this->overheadService->EliminarOverhead($overhead_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";
                return $this->json($resultadoJson);
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];
                return $this->json($resultadoJson);
            }
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }


    }

    /**
     * eliminarOverheads Acción que elimina los overheads seleccionados en la BD
     *
     */
    public function eliminarOverheads(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->overheadService->EliminarOverheads($ids);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";
                return $this->json($resultadoJson);
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];
                return $this->json($resultadoJson);
            }
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }


    }

    /**
     * cargarDatos Acción que carga los datos del overhead en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $overhead_id = $request->get('overhead_id');

        try {
            $resultado = $this->overheadService->CargarDatosOverhead($overhead_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['overhead'] = $resultado['overhead'];

                return $this->json($resultadoJson);
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];

                return $this->json($resultadoJson);
            }
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }

    }
}
