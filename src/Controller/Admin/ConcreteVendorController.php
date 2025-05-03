<?php

namespace App\Controller\Admin;

use App\Utils\Admin\ConcreteVendorService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConcreteVendorController extends AbstractController
{

    private $concreteVendorService;

    public function __construct(ConcreteVendorService $concreteVendorService)
    {
        $this->concreteVendorService = $concreteVendorService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->concreteVendorService->BuscarPermiso($usuario->getUsuarioId(), 21);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                return $this->render('admin/concrete-vendor/index.html.twig', array(
                    'permiso' => $permiso[0]
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
            $total = $this->concreteVendorService->TotalVendors($sSearch);
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

            $data = $this->concreteVendorService->ListarVendors($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

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
     * salvar Acción que inserta un conc vendor en la BD
     *
     */
    public function salvar(Request $request)
    {
        $vendor_id = $request->get('vendor_id');

        $name = $request->get('name');
        $phone = $request->get('phone');
        $address = $request->get('address');
        $contactName = $request->get('contactName');
        $contactEmail = $request->get('contactEmail');

        try {

            if ($vendor_id == "") {
                $resultado = $this->concreteVendorService->SalvarVendor($name, $phone, $address, $contactName, $contactEmail);
            } else {
                $resultado = $this->concreteVendorService->ActualizarVendor($vendor_id, $name, $phone, $address, $contactName, $contactEmail);
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
     * eliminar Acción que elimina un subcontractor en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $vendor_id = $request->get('vendor_id');

        try {
            $resultado = $this->concreteVendorService->EliminarVendor($vendor_id);
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
     * eliminarVendors Acción que elimina los companies seleccionados en la BD
     *
     */
    public function eliminarVendors(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->concreteVendorService->EliminarVendors($ids);
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
     * cargarDatos Acción que carga los datos del subcontractor en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $vendor_id = $request->get('vendor_id');

        try {
            $resultado = $this->concreteVendorService->CargarDatosVendor($vendor_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['vendor'] = $resultado['vendor'];

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
