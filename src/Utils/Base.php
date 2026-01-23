<?php

namespace App\Utils;

use App\Entity\CompanyContact;
use App\Entity\ConcreteVendorContact;
use App\Entity\County;
use App\Entity\DataTracking;
use App\Entity\DataTrackingAttachment;
use App\Entity\DataTrackingConcVendor;
use App\Entity\DataTrackingItem;
use App\Entity\DataTrackingLabor;
use App\Entity\DataTrackingMaterial;
use App\Entity\DataTrackingSubcontract;
use App\Entity\District;
use App\Entity\EstimateEstimator;
use App\Entity\EstimateQuote;
use App\Entity\Holiday;
use App\Entity\InvoiceItem;
use App\Entity\InvoiceItemNotes;
use App\Entity\Item;
use App\Entity\Log;
use App\Entity\Notification;
use App\Entity\PermisoUsuario;
use App\Entity\Project;
use App\Entity\InvoiceAttachment;
use App\Entity\ProjectContact;
use App\Entity\ProjectCounty;
use App\Entity\ProjectItem;
use App\Entity\ProjectItemHistory;
use App\Entity\ProjectNotes;
use App\Entity\InvoiceNotes;
use App\Entity\ProjectPriceAdjustment;
use App\Entity\ReminderRecipient;
use App\Entity\SyncQueueQbwc;
use App\Entity\Unit;
use App\Entity\UserQbwcToken;
use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Registry;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class Base
{
   private $container;
   private $containerBag;
   public $mailer;
   public $security;
   public LoggerInterface $logger;

   public function __construct(
      ContainerInterface    $container,
      MailerInterface       $mailer,
      ContainerBagInterface $containerBag,
      Security              $security,
      LoggerInterface       $logger
   ) {
      $this->container = $container;
      $this->mailer = $mailer;
      $this->containerBag = $containerBag;
      $this->security = $security;
      $this->logger = $logger;
   }

   //doctrine manager
   public function getDoctrine(): Registry
   {
      if (!$this->container->has('doctrine')) {
         throw new \LogicException('The DoctrineBundle is not registered in your application. Try running "composer require symfony/orm-pack".');
      }

      return $this->container->get('doctrine');
   }

   /**
    * Summary of getUser
    * @return UserInterface|null|Usuario
    */
   public function getUser()
   {
      return $this->security->getUser();
   }

   //Generates a URL from the given parameters.
   public function generateUrl(string $route, array $parameters = [], int $referenceType = 1): string
   {
      return $this->container->get('router')->generate($route, $parameters, $referenceType);
   }

   //Returns a rendered view.
   public function renderView(string $view, array $parameters = []): string
   {
      if ($this->container->has('templating')) {
         return $this->container->get('templating')->render($view, $parameters);
      }

      if (!$this->container->has('twig')) {
         throw new \LogicException('You can not use the "renderView" method if the Templating Component or the Twig Bundle are not available. Try running "composer require symfony/twig-bundle".');
      }

      return $this->container->get('twig')->render($view, $parameters);
   }

   //getParameter
   public function getParameter(string $name)
   {
      return $this->containerBag->get($name);
   }

   //Is mobile
   public function isMobile()
   {
      return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
   }

   //escribir log
   public function writelog($txt, $filename = "weblog.txt")
   {
      global $path_logs;

      $datetime = date("Y-m-d H:i:s", time());
      $txt = $datetime . "\t---\t" . $txt . "\n";

      if ($path_logs != "") {
         $filename = $path_logs . $filename;
      }

      $fp = fopen($filename, "a");
      if ($fp) {
         fputs($fp, $txt, strlen($txt));
         fclose($fp);
      } else {
         die("Error IO: writing file '{$filename}'");
      }

      // Puedes usar otros niveles
      $this->logger->info($txt);
   }

   // http://www.the-art-of-web.com/php/truncate/
   function truncate($string, $limit, $break = ".", $pad = "...")
   {
      $string = strip_tags($string);
      // return with no change if string is shorter than $limit
      if (strlen($string) <= $limit)
         return $string;

      // is $break present between $limit and the end of the string?
      $string = substr($string, 0, $limit) . $pad;

      return $string;
   }

   // hacer url
   public function HacerUrl($tex)
   {
      // Código copiado de http://cubiq.org/the-perfect-php-clean-url-generator
      setlocale(LC_ALL, 'en_US.UTF8');
      $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $tex);
      $slug = preg_replace_callback("/[^a-zA-Z0-9\/_|+ -]/", function ($c) {
         return '';
      }, $slug);
      $slug = strtolower(trim($slug, '-'));
      $slug = preg_replace_callback("/[\/_|+ -]+/", function ($c) {
         return '-';
      }, $slug);

      return $slug;

      /*$eliminar = array("!", "¡", "?", "¿", "‘", "\"", "$", "(", ")", ".", ":", ";", "_", "/", "\\", "\$", "%", "@", "#", ",", "«", "»");
        $buscados = array(" ", "á", "é", "í", "ó", "ú", "Á", "É", "Í", "Ó", "Ú", "ñ", "Ñ", "ü", "à", "è", "ì", "ò", "ù", "À", "È", "Ì", "Ò", "Ù");
        $sustitut = array("-", "a", "e", "i", "o", "u", "a", "e", "i", "o", "u", "n", "n", "u", "a", "e", "i", "o", "u", "A", "E", "I", "O", "U");
        $final = strtolower(str_replace($buscados, $sustitut, str_replace($eliminar, "", $cadena)));
        $final = str_replace("–", "-", $final);
        $final = str_replace("–", "-", $final);
        return $final;*/
   }

   //Obterner bien la url
   public function ObtenerURL()
   {
      $s = '';
      if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
         $s = "s";
      }

      $protocol = $this->strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), " / ") . $s;

      $ruta = $this->generateUrl('home');
      if (substr_count($ruta, 'index.php') > 0) {
         $ruta = $this->generateUrl('home') . '../../';
      } else {
         $ruta = $this->generateUrl('home') . '../';;
      }

      $direccion_url = "http" . $s . "://" . $_SERVER['HTTP_HOST'] . $ruta;

      return $direccion_url;
   }

   private function strleft($s1, $s2)
   {
      return substr($s1, 0, strpos($s1, $s2));
   }

   /*Devuelve el ip*/
   public function getIP()
   {

      if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
         $ip = $_SERVER['HTTP_CLIENT_IP'];
      } else {
         if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
         } else {
            $ip = $_SERVER['REMOTE_ADDR'];
         }
      }

      return $ip;
   }

   //Cambiar time zone
   function setTimeZone($zone = 'America/Santiago')
   {
      date_default_timezone_set($zone);
   }


   //Devolver fecha actual
   function ObtenerFechaActual($format = 'Y-m-d')
   {
      $this->setTimeZone();
      $fecha = date($format);

      return $fecha;
   }

   // primer dia del mes
   function ObtenerPrimerDiaMes()
   {
      // Crear un objeto DateTime con la fecha y hora actuales
      $fecha = new \DateTime();

      // Ajustar la fecha al primer día del mes actual
      $fecha->modify('first day of this month');

      // Formatear la fecha en el formato m/d/Y
      $primer_dia_mes = $fecha->format('m/d/Y');

      return $primer_dia_mes;
   }

   //Devuelve la fecha del mensaje en el formato Agosto, 25 H:i
   public function DevolverFechaFormatoBarras($fecha)
   {
      $resultado = "";

      $mes = $fecha->format('m');
      switch ($mes) {
         case "01":
            $mes = "Jan";
            break;
         case "02":
            $mes = "Feb";
            break;
         case "03":
            $mes = "Mar";
            break;
         case "04":
            $mes = "Apr";
            break;
         case "05":
            $mes = "May";
            break;
         case "06":
            $mes = "Jun";
            break;
         case "07":
            $mes = "Jul";
            break;
         case "08":
            $mes = "Ago";
            break;
         case "09":
            $mes = "Sept";
            break;
         case "10":
            $mes = "Oct";
            break;
         case "11":
            $mes = "Nov";
            break;
         case "12":
            $mes = "Dec";
            break;
         default:
            break;
      }

      $resultado = $mes . ", " . $fecha->format('j');

      return $resultado;
   }

   //get size
   public function getSize($archivo)
   {
      $size = 0;
      if (is_file($archivo)) {
         $size = filesize($archivo);
      }

      return $size;
   }

   /**
    * ListarPermisosDeUsuario: Carga todos los permisos de un perfil
    *
    * @param int $usuario_id Id
    *
    * @author Marcel
    */
   public function ListarPermisosDeUsuario($usuario_id)
   {
      $permisos = array();

      /** @var \App\Repository\PermisoUsuarioRepository $permisoUsuarioRepo */
      $permisoUsuarioRepo = $this->getDoctrine()->getRepository(PermisoUsuario::class);
      $usuario_permisos = $permisoUsuarioRepo->ListarPermisosUsuario($usuario_id);
      foreach ($usuario_permisos as $permiso) {

         $ver = $permiso->getVer();
         $agregar = $permiso->getAgregar();
         $editar = $permiso->getEditar();
         $eliminar = $permiso->getEliminar();

         array_push($permisos, array(
            'permiso_id' => $permiso->getPermisoId(),
            'funcion_id' => $permiso->getFuncion()->getFuncionId(),
            'funcion_name' => $permiso->getFuncion()->getDescripcion(),
            'funcion_url' => $permiso->getFuncion()->getUrl(),
            'ver' => ($ver == 1) ? true : false,
            'agregar' => ($agregar == 1) ? true : false,
            'editar' => ($editar == 1) ? true : false,
            'eliminar' => ($eliminar == 1) ? true : false,
            'todos' => ($ver == 1 && $agregar == 1 && $editar == 1 && $eliminar == 1) ? true : false
         ));
      }

      return $permisos;
   }

   //Devolver menu de ican
   public function DevolverMenu($usuario_id)
   {

      $menuInicio = false;
      $menuRol = false;
      $menuUsuario = false;
      $menuLog = false;
      $menuUnit = false;
      $menuItem = false;
      $menuInspector = false;
      $menuCompany = false;
      $menuProject = false;
      $menuDataTracking = false;
      $menuInvoice = false;
      $menuNotification = false;
      $menuEquation = false;
      $menuEmployee = false;
      $menuMaterial = false;
      $menuOverhead = false;
      $menuAdvertisement = false;
      $menuSubcontractor = false;
      $menuReporteSubcontractor = false;
      $menuReporteEmployee = false;
      $menuConcreteVendor = false;
      $menuSchedule = false;
      $menuReminder = false;
      $menuProjectStage = false;
      $menuProjectType = false;
      $menuProposalType = false;
      $menuPlanStatus = false;
      $menuDistrict = false;
      $menuEstimate = false;
      $menuPlanDownloading = false;
      $menuHoliday = false;
      $menuCounty = false;
      $menuPayment = false;
      $menuRace = false;
      $menuEmployeeRrhh = false;
      $menuConcreteClass = false;
      $menuEmployeeRole = false;

      // obtener permisos
      $permisos = $this->ListarPermisosDeUsuario($usuario_id);
      foreach ($permisos as $permiso) {
         if ($permiso['funcion_id'] == 1 && $permiso['ver']) {
            $menuInicio = true;
         }
         if ($permiso['funcion_id'] == 2 && $permiso['ver']) {
            $menuRol = true;
         }
         if ($permiso['funcion_id'] == 3 && $permiso['ver']) {
            $menuUsuario = true;
         }
         if ($permiso['funcion_id'] == 4 && $permiso['ver']) {
            $menuLog = true;
         }
         if ($permiso['funcion_id'] == 5 && $permiso['ver']) {
            $menuUnit = true;
         }
         if ($permiso['funcion_id'] == 6 && $permiso['ver']) {
            $menuItem = true;
         }
         if ($permiso['funcion_id'] == 7 && $permiso['ver']) {
            $menuInspector = true;
         }
         if ($permiso['funcion_id'] == 8 && $permiso['ver']) {
            $menuCompany = true;
         }
         if ($permiso['funcion_id'] == 9 && $permiso['ver']) {
            $menuProject = true;
         }
         if ($permiso['funcion_id'] == 10 && $permiso['ver']) {
            $menuDataTracking = true;
         }
         if ($permiso['funcion_id'] == 11 && $permiso['ver']) {
            $menuInvoice = true;
         }
         if ($permiso['funcion_id'] == 12 && $permiso['ver']) {
            $menuNotification = true;
         }
         if ($permiso['funcion_id'] == 13 && $permiso['ver']) {
            $menuEquation = true;
         }
         if ($permiso['funcion_id'] == 14 && $permiso['ver']) {
            $menuEmployee = true;
         }
         if ($permiso['funcion_id'] == 15 && $permiso['ver']) {
            $menuMaterial = true;
         }
         if ($permiso['funcion_id'] == 16 && $permiso['ver']) {
            $menuOverhead = true;
         }
         if ($permiso['funcion_id'] == 17 && $permiso['ver']) {
            $menuAdvertisement = true;
         }
         if ($permiso['funcion_id'] == 18 && $permiso['ver']) {
            $menuSubcontractor = true;
         }
         if ($permiso['funcion_id'] == 19 && $permiso['ver']) {
            $menuReporteSubcontractor = true;
         }
         if ($permiso['funcion_id'] == 20 && $permiso['ver']) {
            $menuReporteEmployee = true;
         }
         if ($permiso['funcion_id'] == 21 && $permiso['ver']) {
            $menuConcreteVendor = true;
         }
         if ($permiso['funcion_id'] == 22 && $permiso['ver']) {
            $menuSchedule = true;
         }
         if ($permiso['funcion_id'] == 23 && $permiso['ver']) {
            $menuReminder = true;
         }
         if ($permiso['funcion_id'] == 24 && $permiso['ver']) {
            $menuProjectStage = true;
         }
         if ($permiso['funcion_id'] == 25 && $permiso['ver']) {
            $menuProjectType = true;
         }
         if ($permiso['funcion_id'] == 26 && $permiso['ver']) {
            $menuProposalType = true;
         }
         if ($permiso['funcion_id'] == 27 && $permiso['ver']) {
            $menuPlanStatus = true;
         }
         if ($permiso['funcion_id'] == 28 && $permiso['ver']) {
            $menuDistrict = true;
         }
         if ($permiso['funcion_id'] == 29 && $permiso['ver']) {
            $menuEstimate = true;
         }
         if ($permiso['funcion_id'] == 30 && $permiso['ver']) {
            $menuPlanDownloading = true;
         }
         if ($permiso['funcion_id'] == 31 && $permiso['ver']) {
            $menuHoliday = true;
         }
         if ($permiso['funcion_id'] == 32 && $permiso['ver']) {
            $menuCounty = true;
         }
         if ($permiso['funcion_id'] == 33 && $permiso['ver']) {
            $menuPayment = true;
         }

         if ($permiso['funcion_id'] == 34 && $permiso['ver']) {
            $menuRace = true;
         }

         if ($permiso['funcion_id'] == 35 && $permiso['ver']) {
            $menuEmployeeRrhh = true;
         }

         if ($permiso['funcion_id'] == 36 && $permiso['ver']) {
            $menuConcreteClass = true;
         }

         if ($permiso['funcion_id'] == 37 && $permiso['ver']) {
            $menuEmployeeRole = true;
         }
      }

      return [
         'menuInicio' => $menuInicio,
         'menuRol' => $menuRol,
         'menuUsuario' => $menuUsuario,
         'menuLog' => $menuLog,
         'menuUnit' => $menuUnit,
         'menuItem' => $menuItem,
         'menuInspector' => $menuInspector,
         'menuCompany' => $menuCompany,
         'menuProject' => $menuProject,
         'menuDataTracking' => $menuDataTracking,
         'menuInvoice' => $menuInvoice,
         'menuNotification' => $menuNotification,
         'menuEquation' => $menuEquation,
         'menuEmployee' => $menuEmployee,
         'menuMaterial' => $menuMaterial,
         'menuOverhead' => $menuOverhead,
         'menuAdvertisement' => $menuAdvertisement,
         'menuSubcontractor' => $menuSubcontractor,
         'menuReporteSubcontractor' => $menuReporteSubcontractor,
         'menuReporteEmployee' => $menuReporteEmployee,
         'menuConcreteVendor' => $menuConcreteVendor,
         'menuSchedule' => $menuSchedule,
         'menuReminder' => $menuReminder,
         'menuProjectStage' => $menuProjectStage,
         'menuProjectType' => $menuProjectType,
         'menuProposalType' => $menuProposalType,
         'menuPlanStatus' => $menuPlanStatus,
         'menuDistrict' => $menuDistrict,
         'menuEstimate' => $menuEstimate,
         'menuPlanDownloading' => $menuPlanDownloading,
         'menuHoliday' => $menuHoliday,
         'menuCounty' => $menuCounty,
         'menuPayment' => $menuPayment,
         'menuRace' => $menuRace,
         'menuEmployeeRrhh' => $menuEmployeeRrhh,
         'menuConcreteClass' => $menuConcreteClass,
         'menuEmployeeRole' => $menuEmployeeRole,
      ];
   }

   //Devolver primera funcion con permiso
   public function DevolverPrimeraFuncionDeUsuario($usuario_id)
   {
      $funcion = null;

      $permisos = $this->ListarPermisosDeUsuario($usuario_id);
      foreach ($permisos as $permiso) {
         if ($permiso['ver']) {
            $funcion = $permiso;
            break;
         }
      }

      return $funcion;
   }

   /**
    * BuscarPermiso: Busca un permiso determinado
    *
    * @param int $usuario_id Id
    * @param int $funcion_id Id
    * @author Marcel
    */
   public function BuscarPermiso($usuario_id, $funcion_id)
   {
      $permisos = array();

      /** @var \App\Repository\PermisoUsuarioRepository $permisoUsuarioRepo */
      $permisoUsuarioRepo = $this->getDoctrine()->getRepository(PermisoUsuario::class);
      $permiso = $permisoUsuarioRepo->BuscarPermisoUsuario($usuario_id, $funcion_id);
      if ($permiso != null) {
         $ver = $permiso->getVer();
         $agregar = $permiso->getAgregar();
         $editar = $permiso->getEditar();
         $eliminar = $permiso->getEliminar();

         array_push($permisos, array(
            'permiso_id' => $permiso->getPermisoId(),
            'funcion_id' => $permiso->getFuncion()->getFuncionId(),
            'ver' => ($ver == 1) ? true : false,
            'agregar' => ($agregar == 1) ? true : false,
            'editar' => ($editar == 1) ? true : false,
            'eliminar' => ($eliminar == 1) ? true : false,
            'todos' => ($ver == 1 && $agregar == 1 && $editar == 1 && $eliminar == 1) ? true : false
         ));
      }

      return $permisos;
   }

   // subir un archivo
   // src/Utils/Base.php

   public function upload(UploadedFile $file, $dir, $aceptedExtensions = [])
   {
      $fileName = '';

      $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
      // CORRECCIÓN 1: Convertir extensión a minúsculas para evitar errores con "ARCHIVO.PDF"
      $extension = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));

      if ($extension == '') {
         $extension = 'msg';
      }

      // validar extensiones
      $extension_valida = true;
      if (!empty($aceptedExtensions)) {
         // Asegurarse de comparar minúsculas con minúsculas
         $aceptedExtensions = array_map('strtolower', $aceptedExtensions);
         $extension_valida = in_array($extension, $aceptedExtensions);
      }

      if ($extension_valida) {
         $safeFilename = $this->HacerUrl($originalFilename);
         $fileName = $safeFilename . '.' . $extension;

         $i = 1;
         while (file_exists($dir . $fileName)) {
            $fileName = $safeFilename . $i . "." . $extension;
            $i++;
         }

         try {
            // CORRECCIÓN 2: Lanzar el error real si falla
            $file->move($dir, $fileName);
         } catch (\Exception $e) { // Capturar cualquier excepción
            // ESTA ES LA CLAVE: Lanzar el error para verlo en pantalla
            throw new \Exception("Error moving file to '$dir': " . $e->getMessage());
         }
      } else {
         // CORRECCIÓN 3: Avisar si la extensión no es válida
         throw new \Exception("Extension not allowed: .$extension. Allowed: " . implode(', ', $aceptedExtensions));
      }

      return $fileName;
   }
   /**
    * EliminarInformacionDeUsuario
    * @param $usuario_id
    * @return void
    */
   public function EliminarInformacionDeUsuario($usuario_id)
   {
      $em = $this->getDoctrine()->getManager();

      //Eliminar permisos
      /** @var \App\Repository\PermisoUsuarioRepository $permisoUsuarioRepo */
      $permisoUsuarioRepo = $this->getDoctrine()->getRepository(PermisoUsuario::class);
      $permisos_usuario = $permisoUsuarioRepo->ListarPermisosUsuario($usuario_id);
      foreach ($permisos_usuario as $permiso_usuario) {
         $em->remove($permiso_usuario);
      }

      // logs
      /** @var \App\Repository\LogRepository $logRepo */
      $logRepo = $this->getDoctrine()->getRepository(Log::class);
      $logs = $logRepo->ListarLogsDeUsuario($usuario_id);
      foreach ($logs as $log) {
         $em->remove($log);
      }

      // notificaciones
      /** @var \App\Repository\NotificationRepository $notificationRepo */
      $notificationRepo = $this->getDoctrine()->getRepository(Notification::class);
      $notificaciones = $notificationRepo->ListarNotificationsDeUsuario($usuario_id);
      foreach ($notificaciones as $notificacion) {
         $em->remove($notificacion);
      }

      // reminders
      /** @var \App\Repository\ReminderRecipientRepository $reminderRecipientRepo */
      $reminderRecipientRepo = $this->getDoctrine()->getRepository(ReminderRecipient::class);
      $reminders = $reminderRecipientRepo->ListarRemindersDeUsuario($usuario_id);
      foreach ($reminders as $reminder) {
         $em->remove($reminder);
      }

      // estimates
      /** @var \App\Repository\EstimateEstimatorRepository $estimateEstimatorRepo */
      $estimateEstimatorRepo = $this->getDoctrine()->getRepository(EstimateEstimator::class);
      $estimates = $estimateEstimatorRepo->ListarEstimatesDeUsuario($usuario_id);
      foreach ($estimates as $estimate) {
         $em->remove($estimate);
      }

      // qbwc tokens
      /** @var \App\Repository\UserQbwcTokenRepository $userQbwcTokenRepo */
      $userQbwcTokenRepo = $this->getDoctrine()->getRepository(UserQbwcToken::class);
      $qbwc_tokens = $userQbwcTokenRepo->ListarTokensDeUsuario($usuario_id);
      foreach ($qbwc_tokens as $qbwc_token) {
         $em->remove($qbwc_token);
      }

      // project item history
      /** @var \App\Repository\ProjectItemHistoryRepository $historyRepo */
      $historyRepo = $this->getDoctrine()->getRepository(ProjectItemHistory::class);
      $historial = $historyRepo->ListarHistorialDeUsuario($usuario_id);
      foreach ($historial as $historial_item) {
         $em->remove($historial_item);
      }
   }

   // codificar contraseña
   public function CodificarPassword($pass)
   {

      $opciones = [
         'cost' => 12,
      ];
      return password_hash($pass, PASSWORD_BCRYPT, $opciones);
   }

   // verificar contraseña
   public function VerificarPassword($password, $hash)
   {
      return password_verify($password, $hash);
   }

   /**
    * SalvarLog
    * @param $operacion
    * @param $categoria
    * @param $descripcion
    * @return void
    */
   public function SalvarLog($operacion, $categoria, $descripcion)
   {
      $usuario = $this->getUser();

      if ($usuario != null) {
         $em = $this->getDoctrine()->getManager();

         $entity = new Log();

         $entity->setOperacion($operacion);
         $entity->setCategoria($categoria);
         $entity->setDescripcion($descripcion);

         $ip = $this->getIP();
         $entity->setIp($ip);


         $entity->setUsuario($usuario);

         $entity->setFecha(new \DateTime());

         $em->persist($entity);
         $em->flush();
      }
   }

   // ordenar array php
   public function ordenarArrayAsc($array, $prop)
   {
      $lista = $array;

      for ($i = 1; $i < count($lista); $i++) {
         for ($j = 0; $j < count($lista) - $i; $j++) {

            if ($lista[$j]["$prop"] > $lista[$j + 1]["$prop"]) {
               $k = $lista[$j + 1];
               $lista[$j + 1] = $lista[$j];
               $lista[$j] = $k;
            }
         }
      }

      return $lista;
   }

   public function ordenarArrayDesc($array, $prop)
   {
      $lista = $array;

      for ($i = 1; $i < count($lista); $i++) {
         for ($j = 0; $j < count($lista) - $i; $j++) {

            if ($lista[$j]["$prop"] < $lista[$j + 1]["$prop"]) {
               $k = $lista[$j + 1];
               $lista[$j + 1] = $lista[$j];
               $lista[$j] = $k;
            }
         }
      }

      return $lista;
   }

   // funciones para evaluar una ecuacion
   public function evaluateExpression($expression, $xValue)
   {
      // Reemplazar 'x' con el valor proporcionado en la expresión
      $expression = str_ireplace('x', (string)$xValue, $expression);

      // Limpiar la expresión para asegurar que solo contiene números, operadores permitidos y paréntesis
      $expression = preg_replace('/[^0-9+\-*\/(). ]/', '', $expression);

      // Preparar la cadena de evaluación
      $resultado = '';
      $evaluar = '$resultado = ' . $expression . ';';

      // Evaluar la expresión
      eval($evaluar);

      return $resultado; // Salida: El resultado es: 19
   }

   /**
    * ListarYieldsCalculation
    * @return array
    */
   public function ListarYieldsCalculation()
   {
      return [
         [
            'id' => 'none',
            'name' => 'NONE'
         ],
         [
            'id' => 'same',
            'name' => 'SAME AS QUANTITY'
         ],
         [
            'id' => 'equation',
            'name' => 'EQUATION'
         ]
      ];
   }

   /**
    * BuscarYieldCalculation
    * @param $id
    * @return string
    */
   public function BuscarYieldCalculation($id)
   {
      $name = '';

      $lista = $this->ListarYieldsCalculation();
      foreach ($lista as $value) {
         if ($value['id'] == $id) {
            $name = $value['name'];
            break;
         }
      }

      return $name;
   }

   /**
    * DevolverYieldCalculationDeItemProject
    * @param ProjectItem|EstimateQuote $item_entity
    * @return string
    */
   public function DevolverYieldCalculationDeItemProject($item_entity)
   {
      $yield_calculation = $item_entity->getYieldCalculation();

      $yield_calculation_name = $this->BuscarYieldCalculation($yield_calculation);

      // para la ecuacion devuelvo la ecuacion asociada
      if ($yield_calculation == 'equation' && $item_entity->getEquation() != null) {
         $yield_calculation_name = $item_entity->getEquation()->getEquation();
      }

      return $yield_calculation_name;
   }

   /**
    * ListarUltimaNotaDeProject
    * @param $project_id
    * @return array
    */
   public function ListarUltimaNotaDeProject($project_id)
   {
      $nota = null;

      /** @var \App\Repository\ProjectNotesRepository $projectNotesRepo */
      $projectNotesRepo = $this->getDoctrine()->getRepository(ProjectNotes::class);
      $lista = $projectNotesRepo->ListarNotesDeProject($project_id);
      foreach ($lista as $value) {
         $id = $value->getId();

         $notes = strip_tags($value->getNotes());
         $notes = json_encode($notes);

         $nota = [
            'id' => $id,
            'nota' => $this->truncate($notes, 50),
            'date' => $value->getDate()->format('m/d/Y')
         ];
         break;
      }

      return $nota;
   }

   /**
    * AgregarNewItem
    * @param $value
    * @return Item
    */
   public function AgregarNewItem($value, $equation_entity)
   {
      $em = $this->getDoctrine()->getManager();

      $item_entity = new Item();

      $item_entity->setName($value->item);
      $item_entity->setPrice($value->price);
      $item_entity->setStatus(1);
      $item_entity->setYieldCalculation($value->yield_calculation);

      if (isset($value->bone)) {
         $item_entity->setBone($value->bone == 1 || $value->bone === '1' || $value->bone === true);
      }

      if ($value->unit_id != '') {
         $unit = $this->getDoctrine()->getRepository(Unit::class)->find($value->unit_id);
         $item_entity->setUnit($unit);
      }

      $item_entity->setEquation($equation_entity);

      $item_entity->setCreatedAt(new \DateTime());

      $em->persist($item_entity);

      $em->flush();

      return $item_entity;
   }

   /***
    * ListarTodosItems
    * @return array
    */
   public function ListarTodosItems()
   {
      $items = [];

      /** @var \App\Repository\ItemRepository $itemRepo */
      $itemRepo = $this->getDoctrine()->getRepository(Item::class);
      $lista = $itemRepo->ListarOrdenados();
      foreach ($lista as $value) {
         $item = $this->DevolverItem($value);
         $items[] = $item;
      }

      return $items;
   }

   /**
    * DevolverItem
    * @param Item $value
    * @return array
    */
   public function DevolverItem($value)
   {
      $yield_calculation_name = $this->DevolverYieldCalculationDeItem($value);

      return [
         "item_id" => $value->getItemId(),
         "item" => $value->getName(),
         "unit" => $value->getUnit() != null ? $value->getUnit()->getDescription() : '',
         "yield_calculation" => $value->getYieldCalculation(),
         "yield_calculation_name" => $yield_calculation_name,
         "equation_id" => $value->getEquation() != null ? $value->getEquation()->getEquationId() : '',
      ];
   }

   /**
    * DevolverYieldCalculationDeItem
    * @param Item $item_entity
    * @return string
    */
   public function DevolverYieldCalculationDeItem($item_entity)
   {
      $yield_calculation = $item_entity->getYieldCalculation();

      $yield_calculation_name = $this->BuscarYieldCalculation($yield_calculation);

      // para la ecuacion devuelvo la ecuacion asociada
      if ($yield_calculation == 'equation' && $item_entity->getEquation() != null) {
         $yield_calculation_name = $item_entity->getEquation()->getEquation();
      }

      return $yield_calculation_name;
   }

   /**
    * EliminarInformacionRelacionadaDataTracking
    * @param $data_tracking_id
    * @return void
    */
   public function EliminarInformacionRelacionadaDataTracking($data_tracking_id)
   {
      $em = $this->getDoctrine()->getManager();

      // conc vendors
      /** @var \App\Repository\DataTrackingConcVendorRepository $dataTrackingConcVendorRepo */
      $dataTrackingConcVendorRepo = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class);
      $conc_vendors = $dataTrackingConcVendorRepo->ListarConcVendor($data_tracking_id);
      foreach ($conc_vendors as $conc_vendor) {
         $em->remove($conc_vendor);
      }

      // items
      /** @var \App\Repository\DataTrackingItemRepository $dataTrackingItemRepo */
      $dataTrackingItemRepo = $this->getDoctrine()->getRepository(DataTrackingItem::class);
      $items = $dataTrackingItemRepo->ListarItems($data_tracking_id);
      foreach ($items as $item) {
         $em->remove($item);
      }

      // labor
      /** @var \App\Repository\DataTrackingLaborRepository $dataTrackingLaborRepo */
      $dataTrackingLaborRepo = $this->getDoctrine()->getRepository(DataTrackingLabor::class);
      $data_tracking_labors = $dataTrackingLaborRepo->ListarLabor($data_tracking_id);
      foreach ($data_tracking_labors as $data_tracking_labor) {
         $em->remove($data_tracking_labor);
      }

      // materials
      /** @var \App\Repository\DataTrackingMaterialRepository $dataTrackingMaterialRepo */
      $dataTrackingMaterialRepo = $this->getDoctrine()->getRepository(DataTrackingMaterial::class);
      $data_tracking_materials = $dataTrackingMaterialRepo->ListarMaterials($data_tracking_id);
      foreach ($data_tracking_materials as $data_tracking_material) {
         $em->remove($data_tracking_material);
      }

      // data tracking subcontract
      /** @var \App\Repository\DataTrackingSubcontractRepository $dataTrackingSubcontractRepo */
      $dataTrackingSubcontractRepo = $this->getDoctrine()->getRepository(DataTrackingSubcontract::class);
      $subcontract_items = $dataTrackingSubcontractRepo->ListarSubcontracts($data_tracking_id);
      foreach ($subcontract_items as $subcontract_item) {
         $em->remove($subcontract_item);
      }

      // attachments
      $dir = 'uploads/datatracking/';
      /** @var \App\Repository\DataTrackingAttachmentRepository $dataTrackingAttachmentRepo */
      $dataTrackingAttachmentRepo = $this->getDoctrine()->getRepository(DataTrackingAttachment::class);
      $attachments = $dataTrackingAttachmentRepo->ListarAttachmentsDeDataTracking($data_tracking_id);
      foreach ($attachments as $attachment) {

         //eliminar archivo
         $file_eliminar = $attachment->getFile();
         if ($file_eliminar != "" && is_file($dir . $file_eliminar)) {
            unlink($dir . $file_eliminar);
         }

         $em->remove($attachment);
      }
   }

   /**
    * ListarContactsDeProject
    * @param $project_id
    * @return array
    */
   public function ListarContactsDeProject($project_id)
   {
      $contacts = [];

      /** @var \App\Repository\ProjectContactRepository $projectContactRepo */
      $projectContactRepo = $this->getDoctrine()->getRepository(ProjectContact::class);
      $project_contacts = $projectContactRepo->ListarContacts($project_id);
      foreach ($project_contacts as $key => $contact) {
         $contacts[] = [
            'contact_id' => $contact->getContactId(),
            'name' => $contact->getName(),
            'email' => $contact->getEmail(),
            'phone' => $contact->getPhone(),
            'role' => $contact->getRole(),
            'notes' => $contact->getNotes(),
            'posicion' => $key
         ];
      }

      return $contacts;
   }

   /**
    * ListarContactsDeConcreteVendor
    * @param $vendor_id
    * @return array
    */
   public function ListarContactsDeConcreteVendor($vendor_id)
   {
      $contacts = [];

      /** @var \App\Repository\ConcreteVendorContactRepository $concreteVendorContactRepo */
      $concreteVendorContactRepo = $this->getDoctrine()->getRepository(ConcreteVendorContact::class);
      $vendor_contacts = $concreteVendorContactRepo->ListarContacts($vendor_id);
      foreach ($vendor_contacts as $key => $contact) {
         $contacts[] = [
            'contact_id' => $contact->getContactId(),
            'name' => $contact->getName(),
            'email' => $contact->getEmail(),
            'phone' => $contact->getPhone(),
            'role' => $contact->getRole(),
            'notes' => $contact->getNotes(),
            'posicion' => $key
         ];
      }

      return $contacts;
   }

   /***
    * ObtenerSemanasReporteExcel
    * @param string|null $fechaInicio
    * @param string|null $fechaFin
    * @return array
    */
   public function ObtenerSemanasReporteExcel(?string $fechaInicio, ?string $fechaFin, string $formato = 'm/d/Y'): array
   {
      $hoy = new \DateTime();

      if (empty($fechaInicio) && empty($fechaFin)) {
         // Semana actual
         $inicio = (clone $hoy)->modify('monday this week');
         $fin = (clone $hoy)->modify('saturday this week');
      } elseif (empty($fechaInicio)) {
         $fin = \DateTime::createFromFormat($formato, $fechaFin) ?: $hoy;
         $inicio = (clone $fin)->modify('monday this week');
      } elseif (empty($fechaFin)) {
         $inicio = \DateTime::createFromFormat($formato, $fechaInicio) ?: (clone $hoy)->modify('monday this week');
         $fin = clone $hoy;
      } else {
         $inicio = \DateTime::createFromFormat($formato, $fechaInicio);
         $fin = \DateTime::createFromFormat($formato, $fechaFin);
      }

      if (!$inicio || !$fin) {
         return [];
      }

      // Asegurar que las fechas estén en el orden correcto
      if ($inicio > $fin) {
         [$inicio, $fin] = [$fin, $inicio];
      }

      $semanas = [];

      $inicioSemana = (clone $inicio)->modify('monday this week');
      $finSemana = (clone $inicioSemana)->modify('saturday this week');

      while ($inicioSemana <= $fin) {
         $dias = [];
         for ($i = 0; $i < 6; $i++) {
            $dia = (clone $inicioSemana)->modify("+$i days");
            $dias[] = $dia->format($formato);
         }

         $nombre = $dias[0] . ' to ' . end($dias);

         $semanas[] = (object)[
            'nombre' => $nombre,
            'dias' => $dias
         ];

         $inicioSemana->modify('+1 week');
         $finSemana->modify('+1 week');
      }

      return $semanas;
   }

   public function estilizarCelda($sheet, $coord, $styleArray, $bold = false, $align = Alignment::HORIZONTAL_CENTER, $wrapText = false)
   {
      $sheet->getStyle($coord)->applyFromArray($styleArray);
      $sheet->getStyle($coord)->getAlignment()->setHorizontal($align);
      $sheet->getStyle($coord)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

      if ($wrapText) {
         $sheet->getStyle($coord)->getAlignment()->setWrapText(true);
         $sheet->getRowDimension((int)filter_var($coord, FILTER_SANITIZE_NUMBER_INT))->setRowHeight(-1); // auto height
      }

      if ($bold) {
         $sheet->getStyle($coord)->getFont()->setBold(true);
      }
   }

   /**
    * ListarContactsDeCompany
    * @param $company_id
    * @return array
    */
   public function ListarContactsDeCompany($company_id)
   {
      $contacts = [];

      /** @var \App\Repository\CompanyContactRepository $companyContactRepo */
      $companyContactRepo = $this->getDoctrine()->getRepository(CompanyContact::class);
      $company_contacts = $companyContactRepo->ListarContacts($company_id);
      foreach ($company_contacts as $key => $contact) {
         $contacts[] = [
            'contact_id' => $contact->getContactId(),
            'name' => $contact->getName(),
            'email' => $contact->getEmail(),
            'phone' => $contact->getPhone(),
            'role' => $contact->getRole(),
            'notes' => $contact->getNotes(),
            'posicion' => $key
         ];
      }

      return $contacts;
   }

   /**
    * CalcularTotalConcreteYiel
    * @param $data_tracking_id
    * @return float
    */
   public function CalcularTotalConcreteYiel($data_tracking_id)
   {
      $total_conc_yiel = 0;

      /** @var \App\Repository\DataTrackingItemRepository $dataTrackingItemRepo */
      $dataTrackingItemRepo = $this->getDoctrine()->getRepository(DataTrackingItem::class);
      $data_tracking_items = $dataTrackingItemRepo->ListarItems($data_tracking_id);
      foreach ($data_tracking_items as $data_tracking_item) {
         // aplicar el yield
         $quantity_yield = $this->CalcularTotalConcreteYielItem($data_tracking_item);
         $total_conc_yiel += $quantity_yield;
      }

      return $total_conc_yiel;
   }

   /**
    * CalcularTotalConcreteYielItem
    * @param DataTrackingItem $data_tracking_item
    * @return float
    */
   public function CalcularTotalConcreteYielItem($data_tracking_item)
   {

      $quantity_yield = 0;

      if ($data_tracking_item->getProjectItem()->getYieldCalculation() != '' && $data_tracking_item->getProjectItem()->getYieldCalculation() != 'none') {
         if ($data_tracking_item->getProjectItem()->getYieldCalculation() == "equation" && $data_tracking_item->getProjectItem()->getEquation() != null) {
            $quantity = $data_tracking_item->getQuantity();
            $quantity_yield = $this->evaluateExpression($data_tracking_item->getProjectItem()->getEquation()->getEquation(), $quantity);
         } else {
            $quantity_yield = $data_tracking_item->getQuantity();
         }
      }

      return $quantity_yield;
   }

   /**
    * CalcularLostConcrete
    * @param DataTracking $value
    * @return float
    */
   public function CalcularLostConcrete($value)
   {
      $total_conc_item = 0;

      $data_tracking_id = $value->getId();

      /** @var \App\Repository\DataTrackingConcVendorRepository $dataTrackingConcVendorRepo */
      $dataTrackingConcVendorRepo = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class);
      $total_conc_used = $dataTrackingConcVendorRepo->TotalConcUsed($data_tracking_id);

      /** @var \App\Repository\DataTrackingItemRepository $dataTrackingItemRepo */
      $dataTrackingItemRepo = $this->getDoctrine()->getRepository(DataTrackingItem::class);
      $data_tracking_items = $dataTrackingItemRepo->ListarItems($data_tracking_id);
      foreach ($data_tracking_items as $data_tracking_item) {

         // aplicar el yield
         $quantity = $data_tracking_item->getQuantity();
         $quantity_yield = $quantity;
         if ($data_tracking_item->getProjectItem()->getYieldCalculation() == "equation" && $data_tracking_item->getProjectItem()->getEquation() != null) {
            $quantity_yield = $this->evaluateExpression($data_tracking_item->getProjectItem()->getEquation()->getEquation(), $quantity);
         }

         $total_conc_item += $quantity_yield;
      }

      return round($total_conc_used - $total_conc_item, 2);
   }

   /**
    * ListarTodosHolidays
    *
    * @return array
    */
   public function ListarTodosHolidays()
   {
      $holidays = [];

      /** @var \App\Repository\HolidayRepository $holidayRepo */
      $holidayRepo = $this->getDoctrine()->getRepository(Holiday::class);
      $lista = $holidayRepo->ListarOrdenados();
      foreach ($lista as $value) {
         $holidays[] = [
            'holiday_id' => $value->getHolidayId(),
            'fecha' => $value->getDay()->format('Y-m-d'),
            'description' => $value->getDescription(),
         ];
      }

      return $holidays;
   }

   /**
    * ListarCountysDeDistrict
    * @param $district_id
    * @return array
    */
   public function ListarCountysDeDistrict($district_id)
   {
      $arreglo_resultado = [];

      /** @var \App\Repository\CountyRepository $countyRepo */
      $countyRepo = $this->getDoctrine()->getRepository(County::class);
      $lista = $countyRepo->ListarOrdenados("", "", $district_id);
      foreach ($lista as $value) {
         $arreglo_resultado[] = [
            'county_id' => $value->getCountyId(),
            'description' => $value->getDescription()
         ];
      }

      return $arreglo_resultado;
   }

   /**
    * getCountiesDescriptionForProject: Obtiene la descripción de los counties de un project desde la tabla intermedia
    * @param Project $project
    * @return string
    */
   public function getCountiesDescriptionForProject($project)
   {
      if ($project === null || $project->getProjectId() === null) {
         return '';
      }

      $projectCountyRepo = $this->getDoctrine()->getRepository(ProjectCounty::class);
      $projectCounties = $projectCountyRepo->ListarCountysDeProject($project->getProjectId());
      $descriptions = [];
      foreach ($projectCounties as $projectCounty) {
         $county = $projectCounty->getCounty();
         if ($county !== null) {
            $descriptions[] = $county->getDescription();
         }
      }
      return implode(', ', $descriptions);
   }

   /**
    * SalvarNotesUpdate
    * @param $notas
    * @param Project $entity
    * @return void
    */
   public function SalvarNotesUpdate($entity, $notas)
   {
      $em = $this->getDoctrine()->getManager();

      //notes
      foreach ($notas as $value) {

         $project_note = new ProjectNotes();

         $project_note->setNotes($value['notes']);
         $project_note->setDate($value['date']);

         $project_note->setProject($entity);

         $em->persist($project_note);
      }
   }

   /**
    * EliminarInformacionDeInvoice
    * @param $invoice_id
    * @return void
    */
   public function EliminarInformacionDeInvoice($invoice_id)
   {
      $em = $this->getDoctrine()->getManager();

      // items
      /** @var \App\Repository\InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
      $items = $invoiceItemRepo->ListarItems($invoice_id);
      foreach ($items as $item) {

         // notes
         /** @var \App\Repository\InvoiceItemNotesRepository $invoiceItemNotesRepo */
         $invoiceItemNotesRepo = $this->getDoctrine()->getRepository(InvoiceItemNotes::class);
         $notes = $invoiceItemNotesRepo->ListarNotesDeItemInvoice($item->getId());
         foreach ($notes as $note) {
            $em->remove($note);
         }

         $em->remove($item);
      }

      // quickbooks
      /** @var \App\Repository\SyncQueueQbwcRepository $syncQueueQbwcRepo */
      $syncQueueQbwcRepo = $this->getDoctrine()->getRepository(SyncQueueQbwc::class);
      $quickbooks = $syncQueueQbwcRepo->ListarRegistrosDeEntidadId("invoice", $invoice_id);
      foreach ($quickbooks as $quickbook) {
         $em->remove($quickbook);
      }

      // notes
      /** @var \App\Repository\InvoiceNotesRepository $invoiceNotesRepo */
      $invoiceNotesRepo = $this->getDoctrine()->getRepository(InvoiceNotes::class);
      $notes = $invoiceNotesRepo->ListarNotesDeInvoice($invoice_id);
      foreach ($notes as $note) {
         $em->remove($note);
      }

      // attachments
      $dir = 'uploads/invoice/';
      /** @var \App\Repository\InvoiceAttachmentRepository $invoiceAttachmentRepo */
      $invoiceAttachmentRepo = $this->getDoctrine()->getRepository(InvoiceAttachment::class);
      $attachments = $invoiceAttachmentRepo->ListarAttachmentsDeInvoice($invoice_id);
      foreach ($attachments as $attachment) {

         //eliminar archivo
         $file_eliminar = $attachment->getFile();
         if ($file_eliminar != "" && is_file($dir . $file_eliminar)) {
            unlink($dir . $file_eliminar);
         }

         $em->remove($attachment);
      }
   }

   /**
    * ListarPaymentsDeInvoice
    * @param $invoice_id
    * @return array
    */
   public function ListarPaymentsDeInvoice($invoice_id)
   {
      $payments = [];

      /** @var \App\Repository\InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
      $lista = $invoiceItemRepo->ListarItems($invoice_id);
      foreach ($lista as $key => $value) {

         $contract_qty = $value->getProjectItem()->getQuantity();
         $price = $value->getPrice();
         $contract_amount = $contract_qty * $price;

         $quantity_from_previous = $value->getQuantityFromPrevious();
         $unpaid_from_previous = $value->getUnpaidFromPrevious();

         $quantity = $value->getQuantity();
         $quantity_brought_forward = $value->getQuantityBroughtForward();

         // quantity_final debe coincidir con Final Invoice Quantity en invoices: quantity + quantity_brought_forward
         $quantity_final = $quantity + ($quantity_brought_forward ?? 0);

         $quantity_completed = ($quantity + $unpaid_from_previous) + $quantity_from_previous;

         // Invoiced Amount $ debe ser igual a Final Amount This Period en invoices
         // amount_final = quantity_final * price
         $amount = $quantity_final * $price;

         $total_amount = $quantity_completed * $price;

         // payment
         $paid_qty = $value->getPaidQty();
         $paid_amount = $value->getPaidAmount();
         $paid_amount_total = $value->getPaidAmountTotal();

         $unpaid_qty = $value->getUnpaidQty();
         if ($unpaid_qty === null) {
            $unpaid_qty = $quantity_final - $paid_qty;
            $unpaid_qty = max(0, $unpaid_qty);
         }

         $unpaid_qty = max(0, $unpaid_qty);

         // notes
         $notes = $this->ListarNotesDeItemInvoice($value->getId());

         // Verificar si hay historial de cantidad y precio
         $project_item_id = $value->getProjectItem()->getId();
         /** @var \App\Repository\ProjectItemHistoryRepository $historyRepo */
         $historyRepo = $this->getDoctrine()->getRepository(\App\Entity\ProjectItemHistory::class);
         $has_quantity_history = $historyRepo->TieneHistorialCantidad($project_item_id);
         $has_price_history = $historyRepo->TieneHistorialPrecio($project_item_id);

         $payments[] = [
            "invoice_item_id" => $value->getId(),
            "project_item_id" => $project_item_id,

            "apply_retainage" => $value->getProjectItem()->getApplyRetainage(),
            "boned" => $value->getProjectItem()->getBoned() ? 1 : 0,
            "paid_qty"        => $value->getPaidQty(),
            "paid_amount"     => $value->getPaidAmount(),
            "paid_amount_total" => $value->getPaidAmountTotal(),
            // ------------------------------------------
            "item_id" => $value->getProjectItem()->getItem()->getItemId(),
            "item" => $value->getProjectItem()->getItem()->getName(),
            "unit" => $value->getProjectItem()->getItem()->getUnit() != null ? $value->getProjectItem()->getItem()->getUnit()->getDescription() : '',
            "contract_qty" => $contract_qty,
            "price" => $price,
            "contract_amount" => $contract_amount,
            "quantity_from_previous" => $quantity_from_previous,
            "unpaid_from_previous" => $unpaid_from_previous,
            "quantity" => $quantity_final,
            "quantity_completed" => $quantity_completed,
            "amount" => $amount,
            "total_amount" => $total_amount,
            "paid_qty" => $paid_qty,
            "paid_amount" => $paid_amount,
            "paid_amount_total" => $paid_amount_total,
            "unpaid_qty" => $unpaid_qty,
            "principal" => $value->getProjectItem()->getPrincipal(),
            "change_order" => $value->getProjectItem()->getChangeOrder(),
            "change_order_date" => $value->getProjectItem()->getChangeOrderDate() != null ? $value->getProjectItem()->getChangeOrderDate()->format('m/d/Y') : '',
            "has_quantity_history" => $has_quantity_history,
            "has_price_history" => $has_price_history,
            "notes" => $notes,
            "posicion" => $key
         ];
      }

      return $payments;
   }

   /**
    * ListarNotesDeItemInvoice
    * @param $invoice_item_id
    * @return array
    */
   public function ListarNotesDeItemInvoice($invoice_item_id)
   {
      $notes = [];

      /** @var \App\Repository\InvoiceItemNotesRepository $invoiceItemNotesRepo */
      $invoiceItemNotesRepo = $this->getDoctrine()->getRepository(InvoiceItemNotes::class);
      $lista = $invoiceItemNotesRepo->ListarNotesDeItemInvoice($invoice_item_id);
      foreach ($lista as $key => $value) {

         $note = $value->getNotes();
         $note = mb_convert_encoding($note, 'UTF-8', 'UTF-8');

         $notes[] = [
            "id" => $value->getId(),
            "notes" => $note,
            "date" => $value->getDate()->format('m/d/Y'),
            "posicion" => $key
         ];
      }

      return $notes;
   }
}
