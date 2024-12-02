<?php

namespace App\Controller\Admin;

use App\Entity\Funcion;
use App\Utils\Admin\PerfilService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PerfilController extends AbstractController
{

    private $perfilService;

    public function __construct(PerfilService $perfilService)
    {
        $this->perfilService = $perfilService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->perfilService->BuscarPermiso($usuario->getUsuarioId(), 2);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                $funciones = $this->perfilService->getDoctrine()->getRepository(Funcion::class)
                    ->ListarOrdenados();

                return $this->render('admin/rol/index.html.twig', array(
                    'funciones' => $funciones,
                    'permiso' => $permiso[0]
                ));
            }
        } else {
            return $this->redirectToRoute('denegado');
        }
    }

    /**
     * listar Acción que lista los perfiles
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
        $iSortCol_0 = !empty($sort['field']) ? $sort['field'] : 'nombre';
        //$start and $limit
        $pagination = !empty($request->get('pagination')) ? $request->get('pagination') : array();
        $page = !empty($pagination['page']) ? (int)$pagination['page'] : 1;
        $limit = !empty($pagination['perpage']) ? (int)$pagination['perpage'] : -1;
        $start = 0;

        try {
            $pages = 1;
            $total = $this->perfilService->TotalPerfiles($sSearch);
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

            $data = $this->perfilService->ListarPerfiles($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

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
        $perfil_id = $request->get('perfil_id');
        $descripcion = $request->get('descripcion');


        $permisos = $request->get('permisos');
        $permisos = json_decode($permisos);

        try {

            if ($perfil_id == "") {
                $resultado = $this->perfilService->SalvarPerfil($descripcion, $permisos);
            } else {
                $resultado = $this->perfilService->ActualizarPerfil($perfil_id, $descripcion, $permisos);
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
     * eliminar Acción que elimina un perfil en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $perfil_id = $request->get('perfil_id');

        try {
            $resultado = $this->perfilService->EliminarPerfil($perfil_id);
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
     * eliminarPerfiles Acción que elimina los perfiles seleccionados en la BD
     *
     */
    public function eliminarPerfiles(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->perfilService->EliminarPerfiles($ids);
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
     * cargarDatos Acción que carga los datos del perfil en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $perfil_id = $request->get('perfil_id');

        try {
            $resultado = $this->perfilService->CargarDatosPerfil($perfil_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['perfil'] = $resultado['perfil'];

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
     * listarPermisos Acción que lista todos los permisos de un perfil
     *
     */
    public function listarPermisos(Request $request)
    {
        $perfil_id = $request->get('perfil_id');

        try {
            $permisos = $this->perfilService->ListarPermisosDePerfil($perfil_id);

            $resultadoJson['success'] = true;
            $resultadoJson['permisos'] = $permisos;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }

    }

}
