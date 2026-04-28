<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Usuario\ActualizarMisDatosAdminRequest;
use App\Dto\Admin\Usuario\LoginCredentialsRequest;
use App\Dto\Admin\Usuario\UsuarioActualizarRequest;
use App\Dto\Admin\Usuario\UsuarioIdRequest;
use App\Dto\Admin\Usuario\UsuarioIdsRequest;
use App\Dto\Admin\Usuario\UsuarioListarRequest;
use App\Dto\Admin\Usuario\UsuarioSalvarRequest;
use App\Dto\Api\Request\Login\OlvidoContrasennaRequest;
use App\Entity\Funcion;
use App\Entity\Rol;
use App\Entity\Widget;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\FuncionPermissionUiGrouping;
use App\Service\Admin\UsuarioService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UsuarioController extends AbstractAdminController
{
    private $usuarioService;
    private $funcionRepository;
    private $rolRepository;
    private FuncionPermissionUiGrouping $funcionPermissionUiGrouping;

    public function __construct(
        AdminAccessService $adminAccess,
        UsuarioService $usuarioService,
        FuncionPermissionUiGrouping $funcionPermissionUiGrouping,
        private TokenStorageInterface $tokenStorage,
        #[Autowire(service: 'limiter.api_login')]
        private RateLimiterFactory $apiLoginLimiter)
    {
        parent::__construct($adminAccess);
        $this->usuarioService = $usuarioService;
        $this->funcionPermissionUiGrouping = $funcionPermissionUiGrouping;

        $this->funcionRepository = $this->usuarioService->getDoctrine()->getRepository(Funcion::class);
        $this->rolRepository = $this->usuarioService->getDoctrine()->getRepository(Rol::class);
    }

    /**
     * login Acciรณn para mostrar el formulario de login.
     */
    public function login()
    {
        return $this->render('admin/usuario/login.html.twig', []);
    }

    /**
     * forgotpass Acciรณn para mostrar el formulario de login.
     */
    public function forgotpass()
    {
        return $this->render('admin/usuario/forgot-pass.html.twig', []);
    }

    /**
     * autenticar Acción para el chequear el login.
     */
    public function autenticar(Request $request, SessionInterface $session, LoginCredentialsRequest $login): JsonResponse
    {
        $target_path = 'home';
        $email = $login->email;
        $pass = (string) $login->password;
        try {
            $clientIp = $request->getClientIp() ?? '0.0.0.0';
            $loginLimiter = $this->apiLoginLimiter->create($clientIp);
            $rate = $loginLimiter->consume(1);
            if (!$rate->isAccepted()) {
                return $this->json([
                    'success' => false,
                    'error' => 'Too many login attempts. Please wait a few minutes and try again.',
                ], Response::HTTP_TOO_MANY_REQUESTS, [
                    'Retry-After' => (string) max(1, $rate->getRetryAfter()->getTimestamp() - time()),
                ]);
            }

            $resultado = $this->usuarioService->AutenticarLogin($email, $pass);
            if ($resultado['success']) {
                $loginLimiter->reset();
                $entity = $resultado['usuario'];

                // Asegurar que la sesión esté iniciada antes de guardar el token
                if (!$session->isStarted()) {
                    $session->start();
                }

                // Usar UsernamePasswordToken para autenticación normal
                // El firewall name debe ser 'main' que es el firewall que protege /admin
                $token = new UsernamePasswordToken($entity, 'main', $entity->getRoles());

                // Guardar el token en el token storage
                $this->tokenStorage->setToken($token);

                // Guardar la sesión explícitamente para asegurar que el token persista
                $session->set('_security_main', serialize($token));
                $session->save();

                // Obtener permisos del usuario (solo permisos, sin menú ni page config)
                $permisos = $this->usuarioService->ListarPermisosDeUsuario($entity->getUsuarioId());

                // Preparar datos del usuario con permisos (mismo formato que API)
                $usuario_data = [
                    'usuario_id' => $entity->getUsuarioId(),
                    'email' => $entity->getEmail(),
                    'nombre' => $entity->getNombre(),
                    'apellidos' => $entity->getApellidos(),
                    'nombre_completo' => $entity->getNombreCompleto(),
                    'telefono' => $entity->getTelefono(),
                    'rol_id' => $entity->getRol()?->getRolId(),
                    'rol' => $entity->getRol()?->getNombre(),
                    'permisos' => $permisos,
                ];

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['url'] = $this->generateUrl($target_path);
                $resultadoJson['usuario'] = $usuario_data;

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * denegado Perfil de usuario.
     */
    public function denegado()
    {
        $g = $this->adminAccess->exigirUsuarioOlogin($this->getUser());
        if ($g instanceof RedirectResponse) {
            return $g;
        }
        $usuario = $g;

        $funcion = $this->usuarioService->DevolverPrimeraFuncionDeUsuario($usuario->getUsuarioId());

        return $this->render('admin/usuario/denegado.html.twig', [
            'usuario' => $usuario,
            'funcion' => $funcion,
            'puede_ir_dashboard' => $this->adminAccess->usuarioPuedeVer($usuario, FunctionId::HOME),
        ]);
    }

    /**
     * perfil Perfil de usuario.
     */
    public function perfil()
    {
        $g = $this->adminAccess->exigirUsuarioOlogin($this->getUser());
        if ($g instanceof RedirectResponse) {
            return $g;
        }
        $usuario = $g;

        return $this->render('admin/usuario/perfil.html.twig', [
            'usuario' => $usuario,
        ]);
    }

    /**
     * index Perfil de usuario.
     */
    #[RequireAdminPermission(FunctionId::USUARIO)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::USUARIO);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso USUARIO esperado tras #[RequireAdminPermission].');

        $perfiles = $this->rolRepository->ListarOrdenados();
        $funciones = $this->funcionRepository->ListarOrdenados();
        $funcionesAgrupadas = $this->funcionPermissionUiGrouping->group($funciones);

        $widgets = $this->usuarioService->getDoctrine()->getRepository(Widget::class)
           ->findAllOrdered();

        return $this->render('admin/usuario/index.html.twig', [
            'perfiles' => $perfiles,
            'funciones' => $funciones,
            'funcionesAgrupadas' => $funcionesAgrupadas,
            'widgets' => $widgets,
            'permiso' => $permiso,
        ]);
    }

    /**
     * listar Acciรณn que lista los usuarios.
     */
    #[RequireAdminPermission(FunctionId::USUARIO, AdminPermission::View, jsonOnDenied: true)]
    public function listar(UsuarioListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $perfil_id = $listar->perfil_id;
            $estado = $listar->estado;

            // total + data en una sola llamada a tu servicio
            $result = $this->usuarioService->ListarUsuarios(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $perfil_id,
                $estado
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
     * salvar Acciรณn que inserta un usuario en la BD (alta).
     */
    #[RequireAdminPermission(FunctionId::USUARIO, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(UsuarioSalvarRequest $p): JsonResponse
    {
        return $this->persistUsuario('', $p);
    }

    /**
     * actualizar Acción que actualiza un usuario existente.
     */
    #[RequireAdminPermission(FunctionId::USUARIO, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(UsuarioActualizarRequest $dAct): JsonResponse
    {
        $p = UsuarioSalvarRequest::fromActualizarRequest($dAct);

        return $this->persistUsuario((string) $dAct->usuario_id, $p);
    }

    private function persistUsuario(string $usuario_id, UsuarioSalvarRequest $p): JsonResponse
    {
        $rol_id = $p->rol;
        $habilitado = $p->habilitado;
        $contrasenna = $p->password;
        $nombre = $p->nombre;
        $apellidos = $p->apellidos;
        $email = $p->email;
        $permisos = json_decode((string) $p->permisos);
        $widgetAccessRaw = $p->widget_access;
        $widgetAccess = is_string($widgetAccessRaw) && '' !== $widgetAccessRaw ? json_decode($widgetAccessRaw, true) : null;
        $telefono = $p->telefono;
        $estimator = $p->estimator;
        $bond = $p->bond;
        $retainage = $p->retainage;
        $chat = $p->chat;

        $resultadoJson = [];

        try {
            if ('' === $usuario_id) {
                $resultado = $this->usuarioService->SalvarUsuario(
                    $rol_id,
                    $habilitado,
                    $contrasenna,
                    $nombre,
                    $apellidos,
                    $email,
                    $permisos,
                    $telefono,
                    $estimator,
                    $bond,
                    $retainage,
                    $chat,
                    is_array($widgetAccess) ? $widgetAccess : null
                );
            } else {
                $resultado = $this->usuarioService->ActualizarUsuario(
                    $usuario_id,
                    $rol_id,
                    $habilitado,
                    $contrasenna,
                    $nombre,
                    $apellidos,
                    $email,
                    $permisos,
                    $telefono,
                    $estimator,
                    $bond,
                    $retainage,
                    $chat,
                    is_array($widgetAccess) ? $widgetAccess : null
                );
            }

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['usuario_id'] = $resultado['usuario_id'];

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminar Acciรณn que elimina un rol en la BD.
     */
    #[RequireAdminPermission(FunctionId::USUARIO, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(UsuarioIdRequest $dto): JsonResponse
    {
        $usuario_id = $dto->usuario_id;

        try {
            $resultado = $this->usuarioService->EliminarUsuario($usuario_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminarUsuarios Acciรณn que elimina varios usuarios en la BD.
     */
    #[RequireAdminPermission(FunctionId::USUARIO, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarUsuarios(UsuarioIdsRequest $dto): JsonResponse
    {
        $ids = $dto->ids;

        try {
            $resultado = $this->usuarioService->EliminarUsuarios($ids);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * olvidoContrasenna Acciรณn para recuperar la contraseรฑa de un usuario.
     */
    public function olvidoContrasenna(OlvidoContrasennaRequest $dto): JsonResponse
    {
        $email = $dto->email;
        try {
            // Procesar recuperación de contraseña (siempre devuelve éxito para evitar descubrir emails existentes)
            $this->usuarioService->RecuperarContrasenna($email);

            // Siempre devolver éxito independientemente de si el email existe o no
            // Esto previene que usuarios maliciosos descubran emails registrados
            $resultadoJson['success'] = true;
            $resultadoJson['message'] = 'The password recovery process has been started successfully, in a few moments you will receive an email to the address entered';

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            // En caso de error de sistema, aún devolver éxito para no revelar información
            // El error se registra internamente pero no se expone al cliente
            $resultadoJson['success'] = true;
            $resultadoJson['message'] = 'The password recovery process has been started successfully, in a few moments you will receive an email to the address entered';

            return $this->json($resultadoJson);
        }
    }

    /**
     * activarUsuario Acciรณn que activa o desactiva un usuario.
     */
    #[RequireAdminPermission(FunctionId::USUARIO, AdminPermission::Edit, jsonOnDenied: true)]
    public function activarUsuario(UsuarioIdRequest $dto): JsonResponse
    {
        $usuario_id = $dto->usuario_id;

        try {
            $resultado = $this->usuarioService->ActivarDesactivarUsuario($usuario_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * cargarDatos Acciรณn que carga los datos del usuario en la BD.
     */
    #[RequireAdminPermission(FunctionId::USUARIO, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(UsuarioIdRequest $dto): JsonResponse
    {
        $usuario_id = $dto->usuario_id;

        try {
            $resultado = $this->usuarioService->CargarDatosUsuario($usuario_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['usuario'] = $resultado['usuario'];

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * actualizarMisDatos Acciรณn que actualiza el perfil del usuario en la BD.
     */
    public function actualizarMisDatos(ActualizarMisDatosAdminRequest $d): JsonResponse
    {
        $usuario_id = $d->usuario_id;
        $contrasenna_actual = $d->contrasenna_actual;
        $contrasenna = $d->contrasenna;
        $nombre = $d->nombre;
        $apellidos = $d->apellidos;
        $email = $d->email;
        $telefono = $d->telefono;

        try {
            $resultado = $this->usuarioService->ActualizarMisDatos($usuario_id, $contrasenna, $contrasenna_actual, $nombre, $apellidos, $email, $telefono);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * listarOrdenados Acciรณn que lista los usuarios.
     */
    #[RequireAdminPermission(FunctionId::USUARIO, AdminPermission::View, jsonOnDenied: true)]
    public function listarOrdenados(Request $request): JsonResponse
    {
        try {
            $search = $request->get('search');

            $lista = $this->usuarioService->ListarOrdenados($search);

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
