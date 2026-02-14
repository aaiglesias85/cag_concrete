<?php

namespace App\Utils\App;

use App\Entity\Usuario;
use App\Utils\Admin\UsuarioService as AdminUsuarioService;
use App\Utils\Base;
use Symfony\Contracts\Translation\TranslatorInterface;

class UsuarioService extends Base
{
   private AdminUsuarioService $adminUsuarioService;
   private TranslatorInterface $translator;

   public function __construct(
      \Symfony\Component\DependencyInjection\ContainerInterface $container,
      \Symfony\Component\Mailer\MailerInterface $mailer,
      \Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface $containerBag,
      \Symfony\Bundle\SecurityBundle\Security $security,
      \Psr\Log\LoggerInterface $logger,
      AdminUsuarioService $adminUsuarioService,
      TranslatorInterface $translator
   ) {
      parent::__construct($container, $mailer, $containerBag, $security, $logger);
      $this->adminUsuarioService = $adminUsuarioService;
      $this->translator = $translator;
   }

   /**
    * CargarDatosUsuario: Carga los datos del usuario autenticado
    *
    * @return array
    */
   public function CargarDatosUsuario(): array
   {
      $resultado = array();
      $usuario = $this->getUser();

      /** @var Usuario|null $usuario */
      if ($usuario != null) {
         $usuario_id = $usuario->getUsuarioId();
         $resultadoAdmin = $this->adminUsuarioService->CargarDatosUsuario($usuario_id);

         if ($resultadoAdmin['success']) {
            // Agregar imagen y preferred_lang al resultado
            $resultadoAdmin['usuario']['imagen'] = $usuario->getImagen() ?? '';
            $resultadoAdmin['usuario']['preferred_lang'] = $usuario->getPreferredLang();

            $resultado['success'] = true;
            $resultado['usuario'] = $resultadoAdmin['usuario'];
         } else {
            $resultado['success'] = false;
            $resultado['error'] = $resultadoAdmin['error'] ?? $this->translator->trans('usuario.error.cargar_datos', [], 'messages');
         }
      } else {
         $resultado['success'] = false;
         $resultado['error'] = $this->translator->trans('usuario.error.usuario_no_existe', [], 'messages');
      }

      return $resultado;
   }

   /**
    * ActualizarMisDatos: Actualiza los datos del usuario autenticado
    *
    * @param string $nombre
    * @param string $apellidos
    * @param string $email
    * @param string $telefono
    * @param string $password_actual Contraseña actual (opcional)
    * @param string $password Nueva contraseña (opcional)
    * @return array
    */
   public function ActualizarMisDatos($nombre, $apellidos, $email, $telefono, $password_actual = '', $password = '', $preferred_lang = null): array
   {
      $usuario = $this->getUser();

      /** @var Usuario|null $usuario */
      if ($usuario == null) {
         return [
            'success' => false,
            'error' => $this->translator->trans('usuario.error.usuario_no_existe', [], 'messages')
         ];
      }

      $usuario_id = $usuario->getUsuarioId();

      return $this->adminUsuarioService->ActualizarMisDatos(
         $usuario_id,
         $password,
         $password_actual,
         $nombre,
         $apellidos,
         $email,
         $telefono,
         $preferred_lang
      );
   }

   /**
    * ActualizarImagenPerfil: Actualiza la imagen del perfil del usuario autenticado
    *
    * @param string $imagen Nombre del archivo de imagen
    * @return array
    */
   public function ActualizarImagenPerfil($imagen): array
   {
      $usuario = $this->getUser();

      /** @var Usuario|null $usuario */
      if ($usuario == null) {
         return [
            'success' => false,
            'error' => $this->translator->trans('usuario.error.usuario_no_existe', [], 'messages')
         ];
      }

      $usuario_id = $usuario->getUsuarioId();

      return $this->adminUsuarioService->ActualizarImagenPerfil($usuario_id, $imagen);
   }

   /**
    * EliminarImagenPerfil: Elimina la imagen del perfil del usuario autenticado
    *
    * @return array
    */
   public function EliminarImagenPerfil(): array
   {
      $usuario = $this->getUser();

      /** @var Usuario|null $usuario */
      if ($usuario == null) {
         return [
            'success' => false,
            'error' => $this->translator->trans('usuario.error.usuario_no_existe', [], 'messages')
         ];
      }

      $usuario_id = $usuario->getUsuarioId();

      return $this->adminUsuarioService->EliminarImagenPerfil($usuario_id);
   }

   /**
    * Generar cadena aleatoria para nombres de archivo
    *
    * @param int $limit Longitud de la cadena
    * @return string
    */
   public function generarCadenaAleatoria($limit = 6): string
   {
      return $this->adminUsuarioService->generarCadenaAleatoria($limit);
   }
}
