<?php

namespace App\Controller\Admin;

use App\Entity\Funcion;
use App\Entity\Rol;

use App\Utils\Admin\UsuarioService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UsuarioController extends AbstractController
{
    private $usuarioService;
    private $funcionRepository;
    private $rolRepository;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;

        $this->funcionRepository = $this->usuarioService->getDoctrine()->getRepository(Funcion::class);
        $this->rolRepository = $this->usuarioService->getDoctrine()->getRepository(Rol::class);
    }

    /**
     * login Acción para mostrar el formulario de login
     *
     */
    public function login()
    {
        return $this->render('admin/usuario/login.html.twig', array());
    }

    /**
     * autenticar Acción para el chequear el login
     *
     */
    public function autenticar(Request $request, SessionInterface $session)
    {
        $email = $request->get('email');
        $pass = $request->get('password');
        $remember_me = $request->get('remember');
        $target_path = 'home';
        try {
            $resultado = $this->usuarioService->AutenticarLogin($email, $pass);
            if ($resultado['success']) {
                $entity = $resultado['usuario'];

                if ($remember_me == "on") {
                    $token = new RememberMeToken($entity, 'main', "21c48f7d24c39c9137bb0b14b4060a0c");
                    $this->container->get('security.token_storage')->setToken($token);
                } else {
                    $token = new UsernamePasswordToken($entity, null, 'main', $entity->getRoles());
                    $this->container->get('security.token_storage')->setToken($token);
                }

                //$session->set('_security_main', serialize($token));

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['url'] = $this->generateUrl($target_path);
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
     * denegado Perfil de usuario
     *
     */
    public function denegado()
    {
        return $this->render('admin/usuario/denegado.html.twig', array());
    }

    /**
     * perfil Perfil de usuario
     *
     */
    public function perfil()
    {
        $usuario = $this->getUser();

        return $this->render('admin/usuario/perfil.html.twig', array(
            'usuario' => $usuario
        ));
    }

    /**
     * index Perfil de usuario
     *
     */
    public function index()
    {

        $usuario = $this->getUser();
        $permiso = $this->usuarioService->BuscarPermiso($usuario->getUsuarioId(), 3);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                $perfiles = $this->rolRepository->ListarOrdenados();
                $funciones = $this->funcionRepository->ListarOrdenados();

                return $this->render('admin/usuario/index.html.twig', array(
                    'perfiles' => $perfiles,
                    'funciones' => $funciones,
                    'permiso' => $permiso[0]
                ));
            }
        } else {
            return $this->redirectToRoute('denegado');
        }

    }

    /**
     * listar Acción que lista los usuarios
     *
     */
    public function listar(Request $request)
    {
        // search filter by keywords
        $query = !empty($request->get('query')) ? $request->get('query') : array();
        $sSearch = isset($query['generalSearch']) && is_string($query['generalSearch']) ? $query['generalSearch'] : '';
        $empresa_id = isset($query['empresa_id']) && is_string($query['empresa_id']) ? $query['empresa_id'] : '';
        $perfil_id = isset($query['perfil_id']) && is_string($query['perfil_id']) ? $query['perfil_id'] : '';
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
            $total = $this->usuarioService->TotalUsuarios($sSearch, $perfil_id);
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

            $data = $this->usuarioService->ListarUsuarios($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $perfil_id);

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
     * salvar Acción que inserta un usuario en la BD
     *
     */
    public function salvar(Request $request)
    {
        $usuario_id = $request->get('usuario_id');

        $rol_id = $request->get('rol');
        $habilitado = $request->get('habilitado');
        $contrasenna = $request->get('password');
        $nombre = $request->get('nombre');
        $apellidos = $request->get('apellidos');
        $email = $request->get('email');

        $permisos = $request->get('permisos');
        $permisos = json_decode($permisos);

        $telefono = $request->get('telefono');

        $resultadoJson = array();

        try {
            if ($usuario_id == "") {
                $resultado = $this->usuarioService->SalvarUsuario($rol_id, $habilitado, $contrasenna, $nombre, $apellidos,
                    $email, $permisos, $telefono);
            } else {
                $resultado = $this->usuarioService->ActualizarUsuario($usuario_id, $rol_id, $habilitado, $contrasenna, $nombre,
                    $apellidos, $email, $permisos, $telefono);
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
     * eliminar Acción que elimina un rol en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $usuario_id = $request->get('usuario_id');

        try {
            $resultado = $this->usuarioService->EliminarUsuario($usuario_id);
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
     * eliminarUsuarios Acción que elimina varios usuarios en la BD
     *
     */
    public function eliminarUsuarios(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->usuarioService->EliminarUsuarios($ids);
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
     * olvidoContrasenna Acción para recuperar la contraseña de un usuario
     *
     */
    public function olvidoContrasenna(Request $request)
    {
        $email = $request->get('email');
        try {
            $resultado = $this->usuarioService->RecuperarContrasenna($email);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The password recovery process has been started successfully, in a few moments you will receive an email to the address entered";
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
     * activarUsuario Acción que activa o desactiva un usuario
     *
     */
    public function activarUsuario(Request $request)
    {
        $usuario_id = $request->get('usuario_id');

        try {
            $resultado = $this->usuarioService->ActivarDesactivarUsuario($usuario_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
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
     * cargarDatos Acción que carga los datos del usuario en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $usuario_id = $request->get('usuario_id');

        try {
            $resultado = $this->usuarioService->CargarDatosUsuario($usuario_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['usuario'] = $resultado['usuario'];
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
     * actualizarMisDatos Acción que actualiza el perfil del usuario en la BD
     *
     */
    public function actualizarMisDatos(Request $request)
    {

        $usuario_id = $request->get('usuario_id');

        $contrasenna_actual = $request->get('password_actual');
        $contrasenna = $request->get('password');
        $nombre = $request->get('nombre');
        $apellidos = $request->get('apellidos');
        $email = $request->get('email');
        $telefono = $request->get('telefono');

        try {
            $resultado = $this->usuarioService->ActualizarMisDatos($usuario_id, $contrasenna, $contrasenna_actual, $nombre, $apellidos, $email, $telefono);
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
     * listarOrdenados Acción que lista los usuarios
     *
     */
    public function listarOrdenados(Request $request)
    {

        try {

            $lista = $this->usuarioService->ListarOrdenados();

            $resultadoJson['success'] = true;
            $resultadoJson['usuarios'] = $lista;

            return $this->json($resultadoJson);

        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();
            return $this->json($resultadoJson);
        }


    }

}
