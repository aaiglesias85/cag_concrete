<?php

namespace App\Utils;

use App\Entity\Log;
use App\Entity\Notification;
use App\Entity\PermisoUsuario;
use App\Entity\ProjectItem;
use App\Entity\ProjectNotes;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Base
{
    private $container;
    private $containerBag;
    public $mailer;

    public function __construct(ContainerInterface $container, \Swift_Mailer $mailer, ContainerBagInterface $containerBag)
    {
        $this->container = $container;
        $this->mailer = $mailer;
        $this->containerBag = $containerBag;
    }

    //doctrine manager
    public function getDoctrine(): Registry
    {
        if (!$this->container->has('doctrine')) {
            throw new \LogicException('The DoctrineBundle is not registered in your application. Try running "composer require symfony/orm-pack".');
        }

        return $this->container->get('doctrine');
    }

    // user
    public function getUser()
    {
        if (!$this->container->has('security.token_storage')) {
            throw new \LogicException('The SecurityBundle is not registered in your application. Try running "composer require symfony/security-bundle".');
        }

        if (null === $token = $this->container->get('security.token_storage')->getToken()) {
            return;
        }

        if (!\is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }

        return $user;
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
            $ruta = $this->generateUrl('home') . '../';
        } else {
            $ruta = $this->generateUrl('home');
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

    //Remover puntos del rut
    public function LimpiarRut($rut)
    {
        $patron = "[.]";
        $rut = mb_eregi_replace($patron, "", $rut);
        return $rut;
    }

    //Poner puntos del rut
    function FormatearRut($rut)
    {
        if ($rut != "") {
            $rut = $this->LimpiarRut($rut);
            $rutTmp = explode("-", $rut);
            return number_format($rutTmp[0], 0, "", ".") . '-' . $rutTmp[1];
        } else {
            return $rut;
        }

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

    //Generar hexadecimal aleatorio
    function generarCadenaAleatoria()
    {
        $codigo = "";
        //Dos letras
        $codigo .= substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
        //Tres numeros
        $codigo .= "-" . substr(str_shuffle("0123456789"), 0, 6);
        //Tres numeros
        //$codigo .= "-".substr(str_shuffle("0123456789"), 0, 6);

        return $codigo;
    }

    //Generar nombre de paramatero aleatorio
    function generarParamAleatorio()
    {
        $codigo = "";
        //Dos letras
        $codigo .= substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);

        return $codigo;
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

    //Devuelve la fecha del mensaje en el formato Agosto, 25 H:i
    public function DevolverFechaFormatoMensajes($fecha)
    {
        $mes = $fecha->format('m');
        switch ($mes) {
            case "01":
                $mes = "Ene";
                break;
            case "02":
                $mes = "Feb";
                break;
            case "03":
                $mes = "Mar";
                break;
            case "04":
                $mes = "Abr";
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
                $mes = "Dic";
                break;
            default:
                break;
        }

        $resultado = $mes . ", " . $fecha->format('j');

        return $resultado;
    }

    //Devolver mes
    public function DevolverMes($mes)
    {
        $nombre = "";
        switch ($mes) {
            case "1":
                $nombre = "January";
                break;
            case "2":
                $nombre = "February";
                break;
            case "3":
                $nombre = "March";
                break;
            case "4":
                $nombre = "April";
                break;
            case "5":
                $nombre = "May";
                break;
            case "6":
                $nombre = "June";
                break;
            case "7":
                $nombre = "July";
                break;
            case "8":
                $nombre = "August";
                break;
            case "9":
                $nombre = "September";
                break;
            case "10":
                $nombre = "October";
                break;
            case "11":
                $nombre = "November";
                break;
            case "12":
                $nombre = "December";
                break;
            default:
                break;
        }
        return $nombre;
    }

    public function DevolverHora($hora)
    {
        $am = ($hora >= 12) ? "pm" : "am";
        if ($hora > 12) {
            if ($hora == 13) {
                $hora = 1;
            }
            if ($hora == 14) {
                $hora = 2;
            }
            if ($hora == 15) {
                $hora = 3;
            }
            if ($hora == 16) {
                $hora = 4;
            }
            if ($hora == 17) {
                $hora = 5;
            }
            if ($hora == 18) {
                $hora = 6;
            }
            if ($hora == 19) {
                $hora = 7;
            }
            if ($hora == 20) {
                $hora = 8;
            }
            if ($hora == 21) {
                $hora = 9;
            }
            if ($hora == 22) {
                $hora = 10;
            }
            if ($hora == 23) {
                $hora = 11;
            }
            if ($hora == 24) {
                $hora = 12;
            }
        }

        if ($hora == '00') {
            $hora = '12';
        }
        return $hora . $am;
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

        $usuario_permisos = $this->getDoctrine()->getRepository(PermisoUsuario::class)
            ->ListarPermisosUsuario($usuario_id);
        foreach ($usuario_permisos as $permiso) {

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

    //Devolver menu de ican
    public function DevolverMenuIcan($usuario_id)
    {
        $menu = array();

        $permisos = $this->ListarPermisosDeUsuario($usuario_id);
        if (count($permisos) > 0) {
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
            }
            $menu = array(
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
                'menuMaterial' => $menuMaterial
            );
        }

        return $menu;
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

        $permiso = $this->getDoctrine()->getRepository(PermisoUsuario::class)
            ->BuscarPermisoUsuario($usuario_id, $funcion_id);
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
    public function upload(UploadedFile $file, $dir, $aceptedExtensions = [])
    {
        $fileName = '';

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);

        // si es vacio poner msg
        if ($extension == '') {
            $extension = 'msg';
        }

        // validar extensiones
        $extension_valida = true;
        if (!empty($aceptedExtensions)) {
            $extension_valida = in_array($extension, $aceptedExtensions);
        }

        if ($extension_valida) {
            // slug
            $safeFilename = $this->HacerUrl($originalFilename);

            $fileName = $safeFilename . '.' . $extension;

            $i = 1;
            while (file_exists($dir . $fileName)) {
                $fileName = $safeFilename . $i . "." . $extension;
                $i++;
            }

            try {
                $file->move($dir, $fileName);
            } catch (FileException $e) {
                $this->writelog($e->getMessage());
                $fileName = "";
            }
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
        $permisos_usuario = $this->getDoctrine()->getRepository(PermisoUsuario::class)
            ->ListarPermisosUsuario($usuario_id);
        foreach ($permisos_usuario as $permiso_usuario) {
            $em->remove($permiso_usuario);
        }

        // logs
        $logs = $this->getDoctrine()->getRepository(Log::class)
            ->ListarLogsDeUsuario($usuario_id);
        foreach ($logs as $log) {
            $em->remove($log);
        }

        // notificaciones
        $notificaciones = $this->getDoctrine()->getRepository(Notification::class)
            ->ListarNotificationsDeUsuario($usuario_id);
        foreach ($notificaciones as $notificacion) {
            $em->remove($notificacion);
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
     * @param ProjectItem $item_entity
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

        $lista = $this->getDoctrine()->getRepository(ProjectNotes::class)
            ->ListarNotesDeProject($project_id);
        if (!empty($lista)) {
            $nota = [
                'id' => $lista[0]->getId(),
                'nota' => $this->truncate($lista[0]->getNotes(), 50),
                'date' => $lista[0]->getDate()->format('m/d/Y')
            ];
        }

        return $nota;
    }


}