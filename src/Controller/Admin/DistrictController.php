<?php

namespace App\Controller\Admin;

use App\Entity\County;
use App\Utils\Admin\DistrictService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DistrictController extends AbstractController
{
    private $districtService;

    public function __construct(DistrictService $districtService)
    {
        $this->districtService = $districtService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->districtService->BuscarPermiso($usuario->getUsuarioId(), 28);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                return $this->render('admin/district/index.html.twig', array(
                    'permiso' => $permiso[0]
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
            $total = $this->districtService->TotalDistricts($sSearch);
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

            $data = $this->districtService->ListarDistricts($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

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
     * salvar Acción para agregar districts en la BD
     *
     */
    public function salvar(Request $request)
    {
        $district_id = $request->get('district_id');

        $description = $request->get('description');
        $status = $request->get('status');

        try {

            if ($district_id === "") {
                $resultado = $this->districtService->SalvarDistrict($description, $status);
            } else {
                $resultado = $this->districtService->ActualizarDistrict($district_id, $description, $status);
            }

            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['district_id'] = $resultado['district_id'];
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
     * eliminar Acción que elimina un district en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $district_id = $request->get('district_id');

        try {
            $resultado = $this->districtService->EliminarDistrict($district_id);
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
     * eliminarDistricts Acción que elimina los districts seleccionados en la BD
     *
     */
    public function eliminarDistricts(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->districtService->EliminarDistricts($ids);
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
     * cargarDatos Acción que carga los datos del district en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $district_id = $request->get('district_id');

        try {
            $resultado = $this->districtService->CargarDatosDistrict($district_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['district'] = $resultado['district'];

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
