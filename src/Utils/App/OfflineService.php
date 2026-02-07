<?php

namespace App\Utils\App;

use App\Repository\CompanyRepository;
use App\Utils\Base;
use Symfony\Contracts\Translation\TranslatorInterface;

class OfflineService extends Base
{
   private UsuarioService $usuarioService;
   private CompanyRepository $companyRepository;
   private TranslatorInterface $translator;

   public function __construct(
      \Symfony\Component\DependencyInjection\ContainerInterface $container,
      \Symfony\Component\Mailer\MailerInterface $mailer,
      \Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface $containerBag,
      \Symfony\Bundle\SecurityBundle\Security $security,
      \Psr\Log\LoggerInterface $logger,
      UsuarioService $usuarioService,
      CompanyRepository $companyRepository,
      TranslatorInterface $translator
   ) {
      parent::__construct($container, $mailer, $containerBag, $security, $logger);
      $this->usuarioService = $usuarioService;
      $this->companyRepository = $companyRepository;
      $this->translator = $translator;
   }

   /**
    * ListarInformacionRequerida: Lista la información requerida para trabajo offline de la app.
    * Los datos se cargan una vez cuando hay conexión y se trabaja con ellos en memoria/storage.
    *
    * @return array{success: bool, companies?: array, error?: string}
    */
   public function ListarInformacionRequerida(): array
   {
      $resultado = ['success' => false];

      try {
         $resultado['success'] = true;
         $resultado['companies'] = $this->listarCompaniesParaOffline();
         // Aquí se agregan más arrays según se necesiten:
         // $resultado['proyectos'] = $this->listarProyectosParaOffline();
         // $resultado['empleados'] = $this->listarEmpleadosParaOffline();
      } catch (\Exception $e) {
         $resultado['success'] = false;
         $resultado['error'] = $this->translator->trans('message.exception', [], 'messages');
         $this->logger->error($e->getMessage());
      }

      return $resultado;
   }

   /**
    * listarCompaniesParaOffline: Retorna las companies como arrays para uso offline.
    * Usa getArrayResult() para evitar instanciar entidades y el lazy loading (PHP 8.4 en Doctrine 3.4).
    *
    * @return array<array<string, mixed>>
    */
   private function listarCompaniesParaOffline(): array
   {
      $rows = $this->companyRepository->ListarOrdenadosParaOffline();

      return array_map(static function (array $row): array {
         return [
            'company_id' => $row['company_id'],
            'name' => $row['name'],
            'phone' => $row['phone'],
            'address' => $row['address'],
            'contact_name' => $row['contact_name'],
            'contact_email' => $row['contact_email'],
            'email' => $row['email'],
            'website' => $row['website'],
            'created_at' => isset($row['created_at']) && $row['created_at'] instanceof \DateTimeInterface
               ? $row['created_at']->format('c') : $row['created_at'] ?? null,
            'updated_at' => isset($row['updated_at']) && $row['updated_at'] instanceof \DateTimeInterface
               ? $row['updated_at']->format('c') : $row['updated_at'] ?? null,
         ];
      }, $rows);
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
            $resultado['error'] = $resultadoActualizar['error'] ?? $this->translator->trans('offline.error.sincronizar_perfil', [], 'messages');
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
            $resultado['message'] = $this->translator->trans('offline.message.sincronizado', [], 'messages');
            $resultado['usuario'] = $resultadoUsuario['usuario'];
         } else {
            $resultado['error'] = $this->translator->trans('offline.error.cargar_actualizados', [], 'messages');
         }

      } catch (\Exception $e) {
         $resultado['error'] = $this->translator->trans('message.exception', [], 'messages');
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
               $resultado['error'] = $this->translator->trans('offline.error.tipo_no_reconocido', [], 'messages') . ': ' . $tipo;
               break;
         }
      } catch (\Exception $e) {
         $resultado['error'] = $this->translator->trans('message.exception', [], 'messages');
         $this->logger->error($e->getMessage());
      }

      return $resultado;
   }
}
