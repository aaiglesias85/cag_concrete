<?php

namespace App\Controller\Admin;

use App\Entity\Equation;
use App\Entity\Unit;
use App\Utils\Admin\ItemService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ItemController extends AbstractController
{

    private $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->itemService->BuscarPermiso($usuario->getUsuarioId(), 6);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                $units = $this->itemService->getDoctrine()->getRepository(Unit::class)
                    ->ListarOrdenados();

                $equations = $this->itemService->getDoctrine()->getRepository(Equation::class)
                    ->ListarOrdenados();

                $yields_calculation = $this->itemService->ListarYieldsCalculation();

                return $this->render('admin/item/index.html.twig', array(
                    'permiso' => $permiso[0],
                    'units' => $units,
                    'equations' => $equations,
                    'yields_calculation' => $yields_calculation
                ));
            }
        } else {
            return $this->redirectToRoute('denegado');
        }
    }

    /**
     * listar Acción que lista los items
     *
     */
    public function listar(Request $request)
    {

        // search filter by keywords
        $query = !empty($request->get('query')) ? $request->get('query') : array();
        $sSearch = isset($query['generalSearch']) && is_string($query['generalSearch']) ? $query['generalSearch'] : '';
        //Sort
        $sort = !empty($request->get('sort')) ? $request->get('sort') : array();
        $sSortDir_0 = !empty($sort['sort']) ? $sort['sort'] : 'desc';
        $iSortCol_0 = !empty($sort['field']) ? $sort['field'] : 'createdAt';
        //$start and $limit
        $pagination = !empty($request->get('pagination')) ? $request->get('pagination') : array();
        $page = !empty($pagination['page']) ? (int)$pagination['page'] : 1;
        $limit = !empty($pagination['perpage']) ? (int)$pagination['perpage'] : -1;
        $start = 0;

        try {
            $pages = 1;
            $total = $this->itemService->TotalItems($sSearch);
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

            $data = $this->itemService->ListarItems($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

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
        $item_id = $request->get('item_id');

        $unit_id = $request->get('unit_id');
        $description = $request->get('description');
        // $price = $request->get('price');
        $status = $request->get('status');
        $yield_calculation = $request->get('yield_calculation');
        $equation_id = $request->get('equation_id');

        try {

            if ($item_id == "") {
                $resultado = $this->itemService->SalvarItem($unit_id, $description, $status, $yield_calculation, $equation_id);
            } else {
                $resultado = $this->itemService->ActualizarItem($item_id, $unit_id, $description, $status, $yield_calculation, $equation_id);
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
     * eliminar Acción que elimina un item en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $item_id = $request->get('item_id');

        try {
            $resultado = $this->itemService->EliminarItem($item_id);
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
     * eliminarItems Acción que elimina los items seleccionados en la BD
     *
     */
    public function eliminarItems(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->itemService->EliminarItems($ids);
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
     * cargarDatos Acción que carga los datos del item en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $item_id = $request->get('item_id');

        try {
            $resultado = $this->itemService->CargarDatosItem($item_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['item'] = $resultado['item'];

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
