<?php

namespace App\Controller\Admin;

use App\Entity\Funcion;
use App\Entity\Rol;

use App\Http\DataTablesHelper;
use App\Utils\Admin\UsuarioService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\SecurityBundle\Security;

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
    * login Acciรณn para mostrar el formulario de login
    *
    */
   public function login()
   {
      return $this->render('admin/usuario/login.html.twig', array());
   }

   /**
    * forgotpass Acciรณn para mostrar el formulario de login
    *
    */
   public function forgotpass()
   {
      return $this->render('admin/usuario/forgot-pass.html.twig', array());
   }

   /**
    * autenticar Acción para el chequear el login
    *
    */
   public function autenticar(Request $request, SessionInterface $session)
   {
      $email = $request->get('email');
      $pass = $request->get('password');
      $target_path = 'home';
      try {
         $resultado = $this->usuarioService->AutenticarLogin($email, $pass);
         if ($resultado['success']) {
            $entity = $resultado['usuario'];

            // Asegurar que la sesión esté iniciada antes de guardar el token
            if (!$session->isStarted()) {
               $session->start();
            }

            // Usar UsernamePasswordToken para autenticación normal
            // El firewall name debe ser 'main' que es el firewall que protege /admin
            $token = new UsernamePasswordToken($entity, 'main', $entity->getRoles());

            // Guardar el token en el token storage
            $tokenStorage = $this->container->get('security.token_storage');
            $tokenStorage->setToken($token);

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
      $usuario = $this->getUser();

      $funcion = $this->usuarioService->DevolverPrimeraFuncionDeUsuario($usuario->getUsuarioId());

      return $this->render('admin/usuario/denegado.html.twig', array(
         'funcion' => $funcion
      ));
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
    * listar Acciรณn que lista los usuarios
    *
    */
   public function listar(Request $request)
   {
      try {

         // parsear los parametros de la tabla
         $dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['id', 'email', 'nombre', 'apellidos', 'perfil', 'habilitado'],
            defaultOrderField: 'nombre'
         );

         // filtros
         $perfil_id = $request->get('perfil_id');
         $estado = $request->get('estado');

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
            'draw'            => $dt['draw'],
            'data'            => $result['data'],
            'recordsTotal'    => (int) $result['total'],
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
    * salvar Acciรณn que inserta un usuario en la BD
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

      $estimator = $request->get('estimator');

      $resultadoJson = array();

      try {
         if ($usuario_id == "") {
            $resultado = $this->usuarioService->SalvarUsuario(
               $rol_id,
               $habilitado,
               $contrasenna,
               $nombre,
               $apellidos,
               $email,
               $permisos,
               $telefono,
               $estimator
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
               $estimator
            );
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
    * eliminar Acciรณn que elimina un rol en la BD
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
    * eliminarUsuarios Acciรณn que elimina varios usuarios en la BD
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
    * olvidoContrasenna Acciรณn para recuperar la contraseรฑa de un usuario
    *
    */
   public function olvidoContrasenna(Request $request)
   {
      $email = $request->get('email');
      try {
         $resultado = $this->usuarioService->RecuperarContrasenna($email);

         $resultadoJson['success'] = true;
         $resultadoJson['message'] = "The password recovery process has been started successfully, in a few moments you will receive an email to the address entered";
         return $this->json($resultadoJson);
      } catch (\Exception $e) {
         $resultadoJson['success'] = false;
         $resultadoJson['error'] = $e->getMessage();
         return $this->json($resultadoJson);
      }
   }

   /**
    * activarUsuario Acciรณn que activa o desactiva un usuario
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
    * cargarDatos Acciรณn que carga los datos del usuario en la BD
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
    * actualizarMisDatos Acciรณn que actualiza el perfil del usuario en la BD
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
    * listarOrdenados Acciรณn que lista los usuarios
    *
    */
   public function listarOrdenados(Request $request)
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
