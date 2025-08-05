<?php

namespace App\Controller\Admin;

use App\Entity\District;
use App\Utils\Admin\CountyService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CountyController extends AbstractController
{
    private $countyService;

    public function __construct(CountyService $countyService)
    {
        $this->countyService = $countyService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->countyService->BuscarPermiso($usuario->getUsuarioId(), 32);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                // districts
                $districts = $this->countyService->getDoctrine()->getRepository(District::class)
                    ->ListarOrdenados();

                return $this->render('admin/county/index.html.twig', array(
                    'permiso' => $permiso[0],
                    'districts' => $districts,
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
        $district_id = isset($query['district_id']) && is_string($query['district_id']) ? $query['district_id'] : '';

        //Sort
        $sort = !empty($request->get('sort')) ? $request->get('sort') : array();
        $sSortDir_0 = !empty($sort['sort']) ? $sort['sort'] : 'asc';
        $iSortCol_0 = !empty($sort['field']) ? $sort['field'] : 'description';
        //$start and $limit
        $pagination = !empty($request->get('pagination')) ? $request->get('pagination') : array();
        $page = !empty($pagination['page']) ? (int)$pagination['page'] : 1;
        $limit = !empty($pagination['perpage']) ? (int)$pagination['perpage'] : -1;
        $start = 0;

        try {
            $pages = 1;
            $total = $this->countyService->TotalCountys($sSearch, $district_id);
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

            $data = $this->countyService->ListarCountys($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $district_id);

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
     * salvar Acción para agregar countys en la BD
     *
     */
    public function salvar(Request $request)
    {
        $county_id = $request->get('county_id');

        $district_id = $request->get('district_id');
        $description = $request->get('description');
        $status = $request->get('status');

        try {

            if ($county_id === "") {
                $resultado = $this->countyService->SalvarCounty($description, $status, $district_id);
            } else {
                $resultado = $this->countyService->ActualizarCounty($county_id, $description, $status, $district_id);
            }

            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['county_id'] = $resultado['county_id'];
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
     * eliminar Acción que elimina un county en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $county_id = $request->get('county_id');

        try {
            $resultado = $this->countyService->EliminarCounty($county_id);
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
     * eliminarCountys Acción que elimina los countys seleccionados en la BD
     *
     */
    public function eliminarCountys(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->countyService->EliminarCountys($ids);
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
     * cargarDatos Acción que carga los datos del county en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $county_id = $request->get('county_id');

        try {
            $resultado = $this->countyService->CargarDatosCounty($county_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['county'] = $resultado['county'];

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
