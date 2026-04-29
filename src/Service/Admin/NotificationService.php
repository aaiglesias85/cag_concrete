<?php

namespace App\Service\Admin;

use App\Dto\Admin\Notification\NotificationIdRequest;
use App\Dto\Admin\Notification\NotificationIdsRequest;
use App\Dto\Admin\Notification\NotificationListarRequest;
use App\Entity\Notification;
use App\Entity\Usuario;
use App\Repository\NotificationRepository;
use App\Service\Base\Base;

class NotificationService extends Base
{
    /**
     * LeerNotificaciones.
     *
     * @return array
     */
    public function LeerNotificaciones()
    {
        $em = $this->getDoctrine()->getManager();

        $usuario = $this->getUser();
        if (!$usuario instanceof Usuario) {
            return ['success' => false, 'error' => 'Not authenticated'];
        }
        $usuario_id = $usuario->getUsuarioId();

        /** @var NotificationRepository $notificationRepo */
        $notificationRepo = $this->getDoctrine()->getRepository(Notification::class);
        $lista = $notificationRepo->ListarNotificationsDeUsuarioSinLeer($usuario_id);
        foreach ($lista as $value) {
            $value->setReaded(true);
        }

        $em->flush();

        $resultado['success'] = true;

        return $resultado;
    }

    /**
     * EliminarNotification: Elimina un notification en la BD.
     *
     * @author Marcel
     */
    public function EliminarNotification(NotificationIdRequest $dto)
    {
        $notification_id = $dto->notification_id;
        $em = $this->getDoctrine()->getManager();

        $notification = $this->getDoctrine()->getRepository(Notification::class)
           ->find($notification_id);

        if (null != $notification) {
            $em->remove($notification);

            $em->flush();

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The notification does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarNotifications: Elimina los notifications seleccionados en la BD.
     *
     * @author Marcel
     */
    public function EliminarNotifications(NotificationIdsRequest $dto)
    {
        $ids = $dto->ids;
        $em = $this->getDoctrine()->getManager();

        if (!empty($ids)) {
            $ids = explode(',', (string) $ids);
            foreach ($ids as $notification_id) {
                if ('' != $notification_id) {
                    $notification = $this->getDoctrine()->getRepository(Notification::class)
                       ->find($notification_id);
                    if (null != $notification) {
                        $em->remove($notification);
                    }
                }
            }
        }
        $em->flush();

        $resultado['success'] = true;
        $resultado['message'] = 'The operation was successful';

        return $resultado;
    }

    /**
     * ListarNotificationsUltimosDias: Lista los notifications ultimos 30 dias.
     *
     * @param Usuario $usuario
     *
     * @author Marcel
     */
    public function ListarNotificationsUltimosDias($usuario)
    {
        $arreglo_resultado = [];
        $cont = 0;

        $usuario_id = $usuario->getUsuarioId();

        /** @var NotificationRepository $notificationRepo */
        $notificationRepo = $this->getDoctrine()->getRepository(Notification::class);
        $lista = $notificationRepo->ListarNotificationsRangoFecha('', '', 30, (string) $usuario_id, 'DESC');

        foreach ($lista as $value) {
            $arreglo_resultado[$cont]['notification_id'] = $value->getId();
            $arreglo_resultado[$cont]['usuario'] = $value->getUsuario()->getNombre();
            $arreglo_resultado[$cont]['descripcion'] = $value->getContent();
            $arreglo_resultado[$cont]['fecha'] = $this->DevolverFechaFormatoBarras($value->getCreatedAt());
            $arreglo_resultado[$cont]['leida'] = $value->getReaded();
            $arreglo_resultado[$cont]['class'] = $value->getReaded() ? 'm-list-timeline__item--read' : '';
            $arreglo_resultado[$cont]['project_id'] = $value->getProject() ? $value->getProject()->getProjectId() : '';

            ++$cont;
        }

        return $arreglo_resultado;
    }

    /**
     * ListarNotifications: Listar los notifications.
     *
     * @author Marcel
     */
    public function ListarNotifications(NotificationListarRequest $listar, Usuario $viewer)
    {
        $dt = $listar->dt;
        $usuario_id = $viewer->isAdministrador() ? '' : $viewer->getUsuarioId();
        /** @var NotificationRepository $notificationRepo */
        $notificationRepo = $this->getDoctrine()->getRepository(Notification::class);
        $resultado = $notificationRepo->ListarNotificacionesConTotal(
            $dt['start'],
            $dt['length'],
            $dt['search'],
            $dt['orderField'],
            $dt['orderDir'],
            $listar->fecha_inicial,
            $listar->fecha_fin,
            $usuario_id,
            $listar->leida,
        );

        $data = [];

        foreach ($resultado['data'] as $value) {
            $notification_id = $value->getId();

            $data[] = [
                'id' => $notification_id,
                'createdAt' => $value->getCreatedAt()->format('m/d/Y H:i:s'),
                'usuario' => $value->getUsuario()->getNombre(),
                'content' => $value->getContent(),
                'readed' => $value->getReaded() ? 1 : 0,
            ];
        }

        return [
            'data' => $data,
            'total' => $resultado['total'], // ya viene con el filtro aplicado
        ];
    }
}
