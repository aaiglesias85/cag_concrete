<?php

namespace App\Utils\App;

use App\Utils\Base;

class OfflineService extends Base
{
   private UsuarioService $usuarioService;

   public function __construct(
      \Symfony\Component\DependencyInjection\ContainerInterface $container,
      \Symfony\Component\Mailer\MailerInterface $mailer,
      \Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface $containerBag,
      \Symfony\Bundle\SecurityBundle\Security $security,
      \Psr\Log\LoggerInterface $logger,
      UsuarioService $usuarioService
   ) {
      parent::__construct($container, $mailer, $containerBag, $security, $logger);
      $this->usuarioService = $usuarioService;
   }

   /**
    * SincronizarPerfilUsuario: Sincroniza los datos offline del perfil del usuario
    *
    * @param array $profile_offline Datos offline del perfil del usuario
    * @return array
    */
   public function SincronizarPerfilUsuario(array $profile_offline): array
   {
      $resultado = ['success' => false];

      try {
         // Actualizar datos del perfil
         $nombre = $profile_offline['nombre'] ?? null;
         $apellidos = $profile_offline['apellidos'] ?? null;
         $email = $profile_offline['email'] ?? null;
         $telefono = $profile_offline['telefono'] ?? null;
         $password_actual = $profile_offline['passwordactual'] ?? '';
         $password = $profile_offline['password'] ?? '';

         $resultadoActualizar = $this->usuarioService->ActualizarMisDatos(
            $nombre,
            $apellidos,
            $email,
            $telefono,
            $password_actual,
            $password
         );

         if (!$resultadoActualizar['success']) {
            $resultado['error'] = $resultadoActualizar['error'] ?? 'Error al sincronizar los datos del perfil';
            return $resultado;
         }

         // Si hay imagen en los datos offline, sincronizarla también
         if (!empty($profile_offline['imagen'])) {
            $imagen = $profile_offline['imagen'];
            
            // Decodificar imagen base64
            $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imagen));

            if ($data !== false) {
               // Crear directorio si no existe
               $dir = 'uploads/usuario/';
               if (!is_dir($dir)) {
                  mkdir($dir, 0755, true);
               }

               // Generar nombre único para la imagen
               $foto = $this->usuarioService->generarCadenaAleatoria() . ".jpeg";
               $ruta_completa = $dir . $foto;

               file_put_contents($ruta_completa, $data);

               // Actualizar imagen del usuario en BD
               $resultadoImagen = $this->usuarioService->ActualizarImagenPerfil($foto);
               
               if (!$resultadoImagen['success']) {
                  // Si falla la imagen, continuar pero registrar el error
                  $this->logger->error('Error al sincronizar imagen: ' . ($resultadoImagen['error'] ?? 'Unknown error'));
               }
            }
         }

         // Obtener datos actualizados del usuario
         $resultadoUsuario = $this->usuarioService->CargarDatosUsuario();

         if ($resultadoUsuario['success']) {
            $resultado['success'] = true;
            $resultado['message'] = 'Los datos se sincronizaron correctamente';
            $resultado['usuario'] = $resultadoUsuario['usuario'];
         } else {
            $resultado['error'] = 'Error al cargar los datos actualizados';
         }

      } catch (\Exception $e) {
         $resultado['error'] = 'Ha ocurrido un error al procesar la sincronización';
         $this->logger->error($e->getMessage());
      }

      return $resultado;
   }

   /**
    * SincronizarDatosGenericos: Sincroniza datos offline genéricos
    * Este método puede extenderse para sincronizar otros tipos de datos
    *
    * @param string $tipo Tipo de datos a sincronizar (ej: 'perfil', 'ordenes', etc.)
    * @param array $datos Datos offline a sincronizar
    * @return array
    */
   public function SincronizarDatosGenericos(string $tipo, array $datos): array
   {
      $resultado = ['success' => false];

      try {
         switch ($tipo) {
            case 'perfil':
               $resultado = $this->SincronizarPerfilUsuario($datos);
               break;
            
            // Aquí se pueden agregar más tipos de sincronización en el futuro
            // case 'ordenes':
            //    $resultado = $this->SincronizarOrdenes($datos);
            //    break;
            
            default:
               $resultado['error'] = 'Tipo de datos no reconocido: ' . $tipo;
               break;
         }
      } catch (\Exception $e) {
         $resultado['error'] = 'Ha ocurrido un error al procesar la sincronización';
         $this->logger->error($e->getMessage());
      }

      return $resultado;
   }
}
