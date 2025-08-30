<?php

namespace App\Controller\Admin;

use App\Http\DataTablesHelper;
use App\Utils\Admin\NotificationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NotificationController extends AbstractController
{

    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->notificationService->BuscarPermiso($usuario->getUsuarioId(), 12);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                return $this->render('admin/notification/index.html.twig', array(
                    'permiso' => $permiso[0]
                ));
            }
        } else {
            return $this->redirectToRoute('denegado');
        }
    }

    /**
     * listar Acción que lista los usuarios
     *
     */
    public function listar(Request $request)
    {
        try {

            // parsear los parametros de la tabla
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: ['id', 'createdAt', 'usuario', 'content', 'readed' ],
                defaultOrderField: 'createdAt'
            );

            // filtros
            $fecha_inicial = $request->get('fechaInicial');
            $fecha_fin = $request->get('fechaFin');
            $leida = $request->get('leida');

            $usuario = $this->getUser();
            $usuario_id = $usuario->isAdministrador() ? '' : $usuario->getUsuarioId();

            // total + data en una sola llamada a tu servicio
            $result = $this->notificationService->ListarNotifications(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $fecha_inicial,
                $fecha_fin,
                $usuario_id,
                $leida
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
     * eliminar Acción que elimina un notification en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $notification_id = $request->get('notification_id');

        try {
            $resultado = $this->notificationService->EliminarNotification($notification_id);
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
     * eliminarNotifications Acción que elimina los notificationes seleccionados en la BD
     *
     */
    public function eliminarNotifications(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->notificationService->EliminarNotifications($ids);
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
     * leer Acción para leer las notificaciones
     *
     */
    public function leer(Request $request)
    {
        try {
            $resultado = $this->notificationService->LeerNotificaciones();
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

}
