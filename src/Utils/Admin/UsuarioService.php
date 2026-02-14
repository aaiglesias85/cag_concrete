<?php

namespace App\Utils\Admin;

use App\Entity\Blog;
use App\Entity\BlogImagen;
use App\Entity\Empresa;
use App\Entity\EventoUsuario;
use App\Entity\Funcion;
use App\Entity\PermisoUsuario;
use App\Entity\Usuario;
use App\Entity\Rol;
use App\Entity\Cotizacion;
use App\Utils\Base;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class UsuarioService extends Base
{

   /**
    * ListarOrdenados
    * @return array
    */
   public function ListarOrdenados($search = "")
   {
      $usuarios = [];

      /** @var UsuarioRepository $usuarioRepo */
      $usuarioRepo = $this->getDoctrine()->getRepository(Usuario::class);
      $lista = $usuarioRepo->ListarOrdenados($search);
      foreach ($lista as $value) {
         $usuarios[] = [
            'usuario_id' => $value->getUsuarioId(),
            'nombre' => $value->getNombreCompleto(),
            'email' => $value->getEmail(),

         ];
      }

      return $usuarios;
   }

   /**
    * AutenticarLogin: Chequear el login
    *
    * @param string $email Email
    * @param string $pass Pass
    * @author Marcel
    */
   public function AutenticarLogin($email, $pass)
   {
      $resultado = array();

        // primero busco el usuario
      /** @var UsuarioRepository $usuarioRepo */
      $usuarioRepo = $this->getDoctrine()->getRepository(Usuario::class);
      $usuario = $usuarioRepo->BuscarUsuarioPorEmail($email);


      /** @var Usuario $usuario */
      if ($usuario != null && $this->VerificarPassword($pass, $usuario->getContrasenna())) {
         if ($usuario->getHabilitado() == 1) {
            $resultado['success'] = true;
            $resultado['usuario'] = $usuario;
         } else {
            $resultado['success'] = false;
            $resultado['error'] = "Your user has been blocked, please contact your administrator";
         }
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "Invalid credentials";
      }
      return $resultado;
   }

   /**
    * ActualizarMisDatos: Actualiza los datos del usuario en la BD
    *
    * @author Marcel
    */
   public function ActualizarMisDatos($usuario_id, $contrasenna, $contrasenna_actual, $nombre, $apellidos, $email, $telefono, $preferred_lang = null)
   {
      $em = $this->getDoctrine()->getManager();

      $resultado = array();
      $entity = $this->getDoctrine()->getRepository(Usuario::class)->find($usuario_id);
      /**@var Usuario $entity */
      if ($entity != null) {
         //Verificar email
         /** @var \App\Repository\UsuarioRepository $usuarioRepo */
         $usuarioRepo = $this->getDoctrine()->getRepository(Usuario::class);
         $usuario = $usuarioRepo->BuscarUsuarioPorEmail($email);
         if ($usuario != null) {
            if ($entity->getUsuarioId() != $usuario->getUsuarioId()) {
               $resultado['success'] = false;
               $resultado['error'] = "The email address is already assigned to another user.";
               return $resultado;
            }
         }

         // verificar que las contraseñas coincidan
         if ($contrasenna_actual != '' && !$this->VerificarPassword($contrasenna_actual, $entity->getContrasenna())) {
            $resultado['success'] = false;
            $resultado['error'] = "The current password is not correct";
            return $resultado;
         }

         $entity->setNombre($nombre);
         $entity->setApellidos($apellidos);
         $entity->setEmail($email);
         $entity->setTelefono($telefono);

         if ($preferred_lang !== null && $preferred_lang !== '') {
            $entity->setPreferredLang($preferred_lang === 'en' ? 'en' : 'es');
         }

         if ($contrasenna != "") {
            $entity->setContrasenna($this->CodificarPassword($contrasenna));
         }

         $entity->setUpdatedAt(new \DateTime());

         $em->flush();

         //Salvar log
         $nombreCompleto = $entity->getNombreCompleto();
         $log_operacion = "Update";
         $log_categoria = "User";
         $log_descripcion = "The user is modified: $nombreCompleto";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }
      return $resultado;
   }

   /**
    * CargarDatosUsuario: Carga los datos de un usuario
    *
    * @param int $usuario_id Id
    *
    * @author Marcel
    */
   public function CargarDatosUsuario($usuario_id)
   {
      $resultado = array();
      $arreglo_resultado = array();

      $usuario = $this->getDoctrine()->getRepository(Usuario::class)->find($usuario_id);
      /** @var Usuario $usuario */
      if ($usuario != null) {
         $arreglo_resultado['rol'] = $usuario->getRol()->getRolId();

         $arreglo_resultado['nombre'] = $usuario->getNombre();
         $arreglo_resultado['apellidos'] = $usuario->getApellidos();
         $arreglo_resultado['email'] = $usuario->getEmail();
         $arreglo_resultado['telefono'] = $usuario->getTelefono();
         $arreglo_resultado['habilitado'] = $usuario->getHabilitado();
         $arreglo_resultado['estimator'] = $usuario->getEstimator();
         $arreglo_resultado['bond'] = $usuario->getBond();
         $arreglo_resultado['retainage'] = $usuario->getRetainage();
         $arreglo_resultado['preferred_lang'] = $usuario->getPreferredLang();

         $permisos = $this->ListarPermisos($usuario_id);
         $arreglo_resultado['permisos'] = $permisos;

         $resultado['success'] = true;
         $resultado['usuario'] = $arreglo_resultado;
      }
      return $resultado;
   }

   /**
    * ListarPermisos
    * @param $usuario_id
    * @return array
    */
   private function ListarPermisos($usuario_id)
   {
      $permisos = [];
      /** @var PermisoUsuarioRepository $permisoUsuarioRepo */
      $permisoUsuarioRepo = $this->getDoctrine()->getRepository(PermisoUsuario::class);
      $usuario_permisos = $permisoUsuarioRepo->ListarPermisosUsuario($usuario_id);
      foreach ($usuario_permisos as $permiso) {

         $ver = $permiso->getVer();
         $agregar = $permiso->getAgregar();
         $editar = $permiso->getEditar();
         $eliminar = $permiso->getEliminar();

         $permisos[] = [
            'permiso_id' => $permiso->getPermisoId(),
            'funcion_id' => $permiso->getFuncion()->getFuncionId(),
            'ver' => ($ver == 1) ? true : false,
            'agregar' => ($agregar == 1) ? true : false,
            'editar' => ($editar == 1) ? true : false,
            'eliminar' => ($eliminar == 1) ? true : false,
            'todos' => ($ver == 1 && $agregar == 1 && $editar == 1 && $eliminar == 1) ? true : false
         ];
      }

      return $permisos;
   }

   /**
    * ActivarDesactivarUsuario: Activa/Desactiva un usuario
    * @param int $usuario_id Id del usuario
    * @author Marcel
    */
   public function ActivarDesactivarUsuario($usuario_id)
   {
      $resultado = array();
      $em = $this->getDoctrine()->getManager();

      $usuario = $this->getDoctrine()->getRepository(Usuario::class)
         ->find($usuario_id);

      if (!is_null($usuario)) {
         if ($usuario->getHabilitado() == 1) {
            $usuario->setHabilitado(0);
         } else {
            $usuario->setHabilitado(1);
         }
         $em->flush();
         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }
      return $resultado;
   }

   /**
    * RecuperarContrasenna: Recupera la contrasenna de un usuario
    *
    * @param string $email Email del usuario
    * @author Marcel
    */
   public function RecuperarContrasenna($email)
   {
      $resultado = array();
      $em = $this->getDoctrine()->getManager();

      /** @var UsuarioRepository $usuarioRepo */
      $usuarioRepo = $this->getDoctrine()->getRepository(Usuario::class);
      $usuario = $usuarioRepo->BuscarUsuarioPorEmail($email);

      if (!is_null($usuario)) {

         $pass = strval(rand(99, 9999999999));

         //Enviar email
         $direccion_url = $this->ObtenerURL();
         $direccion_from = $this->getParameter('mailer_sender_address');

         $asunto = "Password Recovery Notification";
         $contenido = "Dear user, a new access password has been generated.";
         $contenido .= "Once inside the system you can modify it by entering the section \"My Profile\".<br>";
         $contenido .= "Your new password is: " . $pass . ".<br>";
         $contenido .= "Thank you for preferring our service.";

         $mensaje = new TemplatedEmail();
         $mensaje->subject($asunto)
            ->from($direccion_from)
            ->to($email)
            ->htmlTemplate('admin/mailing/mail.html.twig')
            ->context([
               'direccion_url' => $direccion_url,
               'asunto' => $asunto,
               'receptor' => $usuario->getNombreCompleto(),
               'contenido' => $contenido,
            ]);

         $this->mailer->send($mensaje);

         $usuario->setContrasenna($this->CodificarPassword($pass));
         $em->flush();

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "There is no user in our system for the email entered";
      }

      return $resultado;
   }

   /**
    * EliminarUsuario: Elimina un usuario en la BD
    * @param int $usuario_id Id del usuario
    * @author Marcel
    */
   public function EliminarUsuario($usuario_id)
   {
      $resultado = array();
      $em = $this->getDoctrine()->getManager();

      $usuario = $this->getDoctrine()->getRepository(Usuario::class)
         ->find($usuario_id);
      /** @var Usuario $usuario */
      if ($usuario != null) {

         //Comprarar el usuario actual
         $user_logued = $this->getUser();
         if ($usuario->getUsuarioId() == $user_logued->getUsuarioId()) {
            $resultado['success'] = false;
            $resultado['error'] = "Cannot delete the current user logged in to the system";
            return $resultado;
         }

         // eliminar info
         $this->EliminarInformacionDeUsuario($usuario_id);

         $usuario_nombre = $usuario->getNombreCompleto();

         $em->remove($usuario);

         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "User";
         $log_descripcion = "The user is deleted: $usuario_nombre";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * EliminarUsuarios: Elimina varios usuarios en la BD
    * @param array $$ids Ids
    * @author Marcel
    */
   public function EliminarUsuarios($ids)
   {
      $resultado = array();
      $em = $this->getDoctrine()->getManager();

      if ($ids != "") {
         $ids = explode(',', $ids);
         $cant_eliminada = 0;
         $cant_total = 0;
         foreach ($ids as $usuario_id) {
            if ($usuario_id != "") {
               $cant_total++;
               $usuario = $this->getDoctrine()->getRepository(Usuario::class)
                  ->find($usuario_id);

               if ($usuario != null) {
                  //Comprar el usuario actual
                  $user_logued = $this->getUser();
                  if ($usuario->getUsuarioId() != $user_logued->getUsuarioId()) {

                     $usuario_nombre = $usuario->getNombreCompleto();

                     // eliminar info
                     $this->EliminarInformacionDeUsuario($usuario_id);

                     $em->remove($usuario);
                     $cant_eliminada++;

                     //Salvar log
                     $log_operacion = "Delete";
                     $log_categoria = "User";
                     $log_descripcion = "The user is deleted: $usuario_nombre";
                     $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                  }
               }
            }
         }
      }
      $em->flush();

      if ($cant_eliminada == 0) {
         $resultado['success'] = false;
         $resultado['error'] = "It was not possible to delete any of the users, because they have associated information";
      } else {
         $resultado['success'] = true;

         $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected users because they have associated information";
         $resultado['message'] = $mensaje;
      }

      return $resultado;
   }

   /**
    * ActualizarUsuario: Actualiza los datos del usuario en la BD
    *
    *
    * @author Marcel
    */
   public function ActualizarUsuario($usuario_id, $rol_id, $habilitado, $contrasenna, $nombre, $apellidos, $email, $permisos, $telefono, $estimator, $bond, $retainage)
   {
      $em = $this->getDoctrine()->getManager();

      $resultado = array();
      $entity = $this->getDoctrine()->getRepository(Usuario::class)
         ->find($usuario_id);
      /** @var Usuario $entity */
      if ($entity != null) {
         //Verificar email
         /** @var \App\Repository\UsuarioRepository $usuarioRepo */
         $usuarioRepo = $this->getDoctrine()->getRepository(Usuario::class);
         $usuario = $usuarioRepo->BuscarUsuarioPorEmail($email);
         if ($usuario != null) {
            if ($usuario_id != $usuario->getUsuarioId()) {
               $resultado['success'] = false;
               $resultado['error'] = "The email address is already assigned to another user.";
               return $resultado;
            }
         }

         $entity->setNombre($nombre);
         $entity->setApellidos($apellidos);
         $entity->setEmail($email);
         $entity->setHabilitado($habilitado);
         $entity->setTelefono($telefono);
         $entity->setEstimator($estimator);
         $entity->setBond($bond);
         $entity->setRetainage($retainage);

         if ($contrasenna != "") {
            $entity->setContrasenna($this->CodificarPassword($contrasenna));
         }

         if ($rol_id != '') {
            $rol = $this->getDoctrine()->getRepository(Rol::class)
               ->find($rol_id);
            $entity->setRol($rol);
         }

         $entity->setUpdatedAt(new \DateTime());

         //Permisos
         //Eliminar anteriores
         /** @var \App\Repository\PermisoUsuarioRepository $permisoUsuarioRepo */
         $permisoUsuarioRepo = $this->getDoctrine()->getRepository(PermisoUsuario::class);
         $permisos_usuario = $permisoUsuarioRepo->ListarPermisosUsuario($usuario_id);
         foreach ($permisos_usuario as $permiso_usuario) {
            $em->remove($permiso_usuario);
         }
         if (count($permisos) > 0) {
            foreach ($permisos as $permiso) {
               $funcion_id = $permiso->funcion_id;
               $ver = $permiso->ver ? 1 : 0;
               $agregar = $permiso->agregar ? 1 : 0;
               $editar = $permiso->editar ? 1 : 0;
               $eliminar = $permiso->eliminar ? 1 : 0;

               $funcion = $this->getDoctrine()->getRepository(Funcion::class)
                  ->find($funcion_id);
               if ($funcion != null) {

                  if ($ver == 1 || $agregar == 1 || $editar == 1 || $eliminar == 1) {
                     $permiso_usuario = new PermisoUsuario();

                     $permiso_usuario->setVer($ver);
                     $permiso_usuario->setAgregar($agregar);
                     $permiso_usuario->setEditar($editar);
                     $permiso_usuario->setEliminar($eliminar);

                     $permiso_usuario->setUsuario($entity);
                     $permiso_usuario->setFuncion($funcion);

                     $em->persist($permiso_usuario);
                  }
               }
            }
         }

         $em->flush();

         //Salvar log
         $nombreCompleto = $entity->getNombreCompleto();
         $log_operacion = "Update";
         $log_categoria = "User";
         $log_descripcion = "The user is modified: $nombreCompleto";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }
      return $resultado;
   }

   /**
    * SalvarUsuario: Guarda los datos del usuario en la BD
    *
    *
    * @author Marcel
    */
   public function SalvarUsuario($rol_id, $habilitado, $contrasenna, $nombre, $apellidos, $email, $permisos, $telefono, $estimator, $bond, $retainage)
   {
      $resultado = array();
      $em = $this->getDoctrine()->getManager();

      //Verificar email
      /** @var \App\Repository\UsuarioRepository $usuarioRepo */
      $usuarioRepo = $this->getDoctrine()->getRepository(Usuario::class);
      $usuario = $usuarioRepo->BuscarUsuarioPorEmail($email);
      if ($usuario != null) {
         $resultado['success'] = false;
         $resultado['error'] = "The email address is already assigned to another user.";
         return $resultado;
      }
      $entity = new Usuario();

      $entity->setNombre($nombre);
      $entity->setApellidos($apellidos);
      $entity->setEmail($email);
      $entity->setContrasenna($this->CodificarPassword($contrasenna));
      $entity->setTelefono($telefono);
      $entity->setHabilitado($habilitado);
      $entity->setEstimator($estimator);
      $entity->setBond($bond);
      $entity->setRetainage($retainage);

      if ($rol_id != '') {
         $rol = $this->getDoctrine()->getRepository(Rol::class)
            ->find($rol_id);
         $entity->setRol($rol);
      }

      $entity->setCreatedAt(new \DateTime());

      $em->persist($entity);

      //Permisos
      if (count($permisos) > 0) {
         foreach ($permisos as $permiso) {
            $funcion_id = $permiso->funcion_id;
            $ver = $permiso->ver ? 1 : 0;
            $agregar = $permiso->agregar ? 1 : 0;
            $editar = $permiso->editar ? 1 : 0;
            $eliminar = $permiso->eliminar ? 1 : 0;

            $funcion = $this->getDoctrine()->getRepository(Funcion::class)
               ->find($funcion_id);
            if ($funcion != null) {

               if ($ver == 1 || $agregar == 1 || $editar == 1 || $eliminar == 1) {
                  $permiso_usuario = new PermisoUsuario();

                  $permiso_usuario->setVer($ver);
                  $permiso_usuario->setAgregar($agregar);
                  $permiso_usuario->setEditar($editar);
                  $permiso_usuario->setEliminar($eliminar);

                  $permiso_usuario->setUsuario($entity);
                  $permiso_usuario->setFuncion($funcion);

                  $em->persist($permiso_usuario);
               }
            }
         }
      }

      $em->flush();

      //Salvar log
      $nombreCompleto = $entity->getNombreCompleto();
      $log_operacion = "Add";
      $log_categoria = "User";
      $log_descripcion = "The user is added: $nombreCompleto";
      $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

      $resultado['success'] = true;

      return $resultado;
   }

   /**
    * ListarUsuarios: Listar los usuarios
    *
    * @param int $start Inicio
    * @param int $limit Limite
    * @param string $sSearch Para buscar
    *
    * @author Marcel
    */
   public function ListarUsuarios(int $start, int $limit, ?string $sSearch, ?string $iSortCol_0, ?string $sSortDir_0, ?string $perfil_id, ?string $estado)
   {
      /** @var \App\Repository\UsuarioRepository $usuarioRepo */
      $usuarioRepo = $this->getDoctrine()->getRepository(Usuario::class);
      $resultado = $usuarioRepo->ListarUsuariosConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $perfil_id, $estado);

      $data = [];

      foreach ($resultado['data'] as $value) {
         $usuario_id = $value->getUsuarioId();

         $data[] = array(
            "id" => $usuario_id,
            'email' => $value->getEmail(),
            'nombre' => $value->getNombre(),
            'apellidos' => $value->getApellidos(),
            'estado' => ($value->getHabilitado()) ? 1 : 0,
            'perfil' => $value->getRol()->getNombre(),
         );
      }

      return [
         'data'  => $data,
         'total' => $resultado['total'], // ya viene con el filtro aplicado
      ];
   }

   /**
    * ActualizarImagenPerfil: Actualiza la imagen del perfil del usuario
    *
    * @param int $usuario_id Id del usuario
    * @param string $imagen Nombre del archivo de imagen
    * @return array
    * @author Marcel
    */
   public function ActualizarImagenPerfil($usuario_id, $imagen)
   {
      $em = $this->getDoctrine()->getManager();
      $resultado = array();
      $entity = $this->getDoctrine()->getRepository(Usuario::class)->find($usuario_id);

      /** @var Usuario $entity */
      if ($entity != null) {
         // Eliminar foto anterior
         $imagenOld = $entity->getImagen();
         if ($imagenOld != "" && $imagen != $imagenOld) {
            $dir = 'uploads/usuario/';
            if (is_file($dir . $imagenOld)) {
               unlink($dir . $imagenOld);
            }
         }

         $entity->setImagen($imagen);
         $em->flush();

         // Salvar log
         $nombreCompleto = $entity->getNombreCompleto();
         $log_operacion = "Update";
         $log_categoria = "User";
         $log_descripcion = "The user profile image is modified: $nombreCompleto";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
         $resultado['imagen'] = $imagen;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * EliminarImagenPerfil: Elimina la imagen del perfil del usuario
    *
    * @param int $usuario_id Id del usuario
    * @return array
    * @author Marcel
    */
   public function EliminarImagenPerfil($usuario_id)
   {
      $em = $this->getDoctrine()->getManager();
      $resultado = array();
      $entity = $this->getDoctrine()->getRepository(Usuario::class)->find($usuario_id);

      /** @var Usuario $entity */
      if ($entity != null) {
         // Eliminar foto del servidor
         $imagen = $entity->getImagen();
         if ($imagen != "") {
            $dir = 'uploads/usuario/';
            if (is_file($dir . $imagen)) {
               unlink($dir . $imagen);
            }
         }

         $entity->setImagen(null);
         $em->flush();

         // Salvar log
         $nombreCompleto = $entity->getNombreCompleto();
         $log_operacion = "Update";
         $log_categoria = "User";
         $log_descripcion = "The user profile image is deleted: $nombreCompleto";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * Generar cadena aleatoria para nombres de archivo
    *
    * @param int $limit Longitud de la cadena
    * @return string
    */
   public function generarCadenaAleatoria($limit = 6): string
   {
      $codigo = "";
      // Letras
      $codigo .= substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $limit);
      // Números
      $codigo .= "-" . substr(str_shuffle("0123456789"), 0, $limit);

      return $codigo;
   }
}
