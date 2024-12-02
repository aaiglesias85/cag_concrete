<?php

namespace App\Utils\Admin;

use App\Entity\Notification;
use App\Entity\Usuario;
use App\Utils\Base;

class NotificationService extends Base
{

    /**
     * LeerNotificaciones
     * @return array
     */
    public function LeerNotificaciones()
    {
        $em = $this->getDoctrine()->getManager();

        $usuario = $this->getUser();
        $usuario_id = $usuario->getUsuarioId();

        $lista = $this->getDoctrine()->getRepository(Notification::class)
            ->ListarNotificationsDeUsuarioSinLeer($usuario_id);
        foreach ($lista as $value) {
            $value->setReaded(1);
        }

        $em->flush();

        $resultado['success'] = true;
        return $resultado;
    }

    /**
     * EliminarNotification: Elimina un notification en la BD
     * @param int $notification_id Id
     * @author Marcel
     */
    public function EliminarNotification($notification_id)
    {
        $em = $this->getDoctrine()->getManager();

        $notification = $this->getDoctrine()->getRepository(Notification::class)
            ->find($notification_id);

        if ($notification != null) {

            $em->remove($notification);

            $em->flush();

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The notification does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarNotifications: Elimina los notifications seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarNotifications($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            foreach ($ids as $notification_id) {
                if ($notification_id != "") {
                    $notification = $this->getDoctrine()->getRepository(Notification::class)
                        ->find($notification_id);
                    if ($notification != null) {
                        $em->remove($notification);
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
     * ListarNotificationsUltimosDias: Lista los notifications ultimos 30 dias
     * @param Usuario $usuario
     *
     * @author Marcel
     */
    public function ListarNotificationsUltimosDias($usuario)
    {
        $arreglo_resultado = array();
        $cont = 0;

        $usuario_id = ($usuario->isAdministrador()) ? "" : $usuario->getUsuarioId();

        $lista = $this->getDoctrine()->getRepository(Notification::class)
            ->ListarNotificationsRangoFecha("", "", 30, $usuario_id, 'DESC');

        foreach ($lista as $value) {

            $arreglo_resultado[$cont]['notification_id'] = $value->getId();
            $arreglo_resultado[$cont]['usuario'] = $value->getUsuario()->getNombre();
            $arreglo_resultado[$cont]['descripcion'] = $value->getContent();
            $arreglo_resultado[$cont]['fecha'] = $this->DevolverFechaFormatoBarras($value->getCreatedAt());
            $arreglo_resultado[$cont]['leida'] = $value->getReaded();
            $arreglo_resultado[$cont]['class'] = $value->getReaded() ? 'm-list-timeline__item--read' : '';

            $cont++;
        }

        return $arreglo_resultado;
    }

    /**
     * ListarNotifications: Listar los notifications
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarNotifications($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $fecha_inicial, $fecha_fin, $usuario_id)
    {
        $arreglo_resultado = array();
        $cont = 0;

        $lista = $this->getDoctrine()->getRepository(Notification::class)
            ->ListarNotifications($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $fecha_inicial, $fecha_fin, $usuario_id);

        foreach ($lista as $value) {
            $notification_id = $value->getId();

            $acciones = $this->ListarAcciones($notification_id);

            $arreglo_resultado[$cont] = array(
                "id" => $notification_id,
                "createdAt" => $value->getCreatedAt()->format("m/d/Y H:i:s"),
                "usuario" => $value->getUsuario()->getNombre(),
                "content" => $value->getContent(),
                "readed" => $value->getReaded() ? 1 : 0,
                "acciones" => $acciones
            );


            $cont++;
        }

        return $arreglo_resultado;
    }

    /**
     * TotalNotifications: Total de notifications
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalNotifications($sSearch, $fecha_inicial, $fecha_fin, $usuario_id)
    {
        $total = $this->getDoctrine()->getRepository(Notification::class)
            ->TotalNotifications($sSearch, $fecha_inicial, $fecha_fin, $usuario_id);

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
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 12);

        $acciones = "";

        if (count($permiso) > 0) {
            if ($permiso[0]['eliminar']) {
                $acciones .= ' <a href="javascript:;" class="delete m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete record" data-id="' . $id . '"><i class="la la-trash"></i></a>';
            }
        }

        return $acciones;
    }
}