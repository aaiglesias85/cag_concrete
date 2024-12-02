<?php

namespace App\Utils\Admin;

use App\Entity\Log;
use App\Entity\Usuario;
use App\Utils\Base;

class LogService extends Base
{

    /**
     * EliminarLog: Elimina un log en la BD
     * @param int $log_id Id
     * @author Marcel
     */
    public function EliminarLog($log_id)
    {
        $em = $this->getDoctrine()->getManager();

        $log = $this->getDoctrine()->getRepository(Log::class)
            ->find($log_id);

        if ($log != null) {

            $em->remove($log);

            $em->flush();

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The log does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarLogs: Elimina los logs seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarLogs($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            foreach ($ids as $log_id) {
                if ($log_id != "") {
                    $log = $this->getDoctrine()->getRepository(Log::class)
                        ->find($log_id);
                    if ($log != null) {
                        $em->remove($log);
                    }
                }
            }
        }
        $em->flush();

        $resultado['success'] = true;
        $resultado['message'] = "The operation was successful";

        return $resultado;
    }

    /**
     * ListarLogsUltimosDias: Lista los logs ultimos 30 dias
     * @param Usuario $usuario
     *
     * @author Marcel
     */
    public function ListarLogsUltimosDias($usuario)
    {
        $arreglo_resultado = array();
        $cont = 0;

        $usuario_id = ($usuario->isAdministrador()) ? "" : $usuario->getUsuarioId();

        $lista = $this->getDoctrine()->getRepository(Log::class)
            ->ListarLogsRangoFecha("", "", 30, $usuario_id, 'DESC');

        foreach ($lista as $value) {

            $arreglo_resultado[$cont]['log_id'] = $value->getLogId();
            $arreglo_resultado[$cont]['usuario'] = $value->getUsuario()->getNombre();
            $arreglo_resultado[$cont]['categoria'] = $value->getCategoria();
            $arreglo_resultado[$cont]['descripcion'] = $value->getDescripcion();
            $arreglo_resultado[$cont]['fecha'] = $this->DevolverFechaFormatoBarras($value->getFecha());

            $operacion = $value->getOperacion();
            $arreglo_resultado[$cont]['operacion'] = $operacion;

            $arreglo_resultado[$cont]['class'] = 'm-badge--success';
            $arreglo_resultado[$cont]['class2'] = 'm-widget2__item--success';
            if ($operacion == "Update") {
                $arreglo_resultado[$cont]['class'] = 'm-badge--info';
                $arreglo_resultado[$cont]['class2'] = 'm-widget2__item--info';
            }
            if ($operacion == "Delete") {
                $arreglo_resultado[$cont]['class'] = 'm-badge--danger';
                $arreglo_resultado[$cont]['class2'] = 'm-widget2__item--danger';
            }

            $cont++;
        }

        return $arreglo_resultado;
    }

    /**
     * ListarLogs: Listar los logs
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarLogs($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $fecha_inicial, $fecha_fin, $usuario_id)
    {
        $arreglo_resultado = array();
        $cont = 0;

        $lista = $this->getDoctrine()->getRepository(Log::class)
            ->ListarLogs($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $fecha_inicial, $fecha_fin, $usuario_id);

        foreach ($lista as $value) {
            $log_id = $value->getLogId();

            $acciones = $this->ListarAcciones($log_id);

            $arreglo_resultado[$cont] = array(
                "id" => $log_id,
                "fecha" => $value->getFecha()->format("m/d/Y H:i:s"),
                "nombre" => $value->getUsuario()->getNombre(),
                "operacion" => $value->getOperacion(),
                "categoria" => $value->getCategoria(),
                "descripcion" => $value->getDescripcion(),
                "ip" => $value->getIp(),
                "acciones" => $acciones
            );


            $cont++;
        }

        return $arreglo_resultado;
    }

    /**
     * TotalLogs: Total de logs
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalLogs($sSearch, $fecha_inicial, $fecha_fin, $usuario_id)
    {
        $total = $this->getDoctrine()->getRepository(Log::class)
            ->TotalLogs($sSearch, $fecha_inicial, $fecha_fin, $usuario_id);

        return $total;
    }

    /**
     * ListarAcciones: Lista los permisos de un usuario de la BD
     *
     * @author Marcel
     */
    public function ListarAcciones($id)
    {
        $usuario = $this->getUser();
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 4);

        $acciones = "";

        if (count($permiso) > 0) {
            if ($permiso[0]['eliminar']) {
                $acciones .= ' <a href="javascript:;" class="delete m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete record" data-id="' . $id . '"><i class="la la-trash"></i></a>';
            }
        }

        return $acciones;
    }
}