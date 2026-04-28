<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Notification\NotificationIdRequest;
use App\Dto\Admin\Notification\NotificationIdsRequest;
use App\Dto\Admin\Notification\NotificationListarRequest;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\NotificationService;
use Symfony\Component\HttpFoundation\JsonResponse;

class NotificationController extends AbstractAdminController
{
    private $notificationService;

    public function __construct(
        AdminAccessService $adminAccess,
        NotificationService $notificationService)
    {
        parent::__construct($adminAccess);
        $this->notificationService = $notificationService;
    }

    #[RequireAdminPermission(FunctionId::NOTIFICATION)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::NOTIFICATION);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso NOTIFICATION esperado tras #[RequireAdminPermission].');

        return $this->render('admin/notification/index.html.twig', [
            'permiso' => $permiso,
        ]);
    }

    /**
     * listar Acción que lista los usuarios.
     */
    #[RequireAdminPermission(FunctionId::NOTIFICATION, AdminPermission::View, jsonOnDenied: true)]
    public function listar(NotificationListarRequest $listar): JsonResponse
    {
        try {
            $usuario = $this->DevolverUsuario();

            $dt = $listar->dt;

            $fecha_inicial = $listar->fecha_inicial;
            $fecha_fin = $listar->fecha_fin;
            $leida = $listar->leida;

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
                'draw' => $dt['draw'],
                'data' => $result['data'],
                'recordsTotal' => (int) $result['total'],
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
     * eliminar Acción que elimina un notification en la BD.
     */
    #[RequireAdminPermission(FunctionId::NOTIFICATION, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(NotificationIdRequest $dto): JsonResponse
    {
        $notification_id = $dto->notification_id;

        try {
            $resultado = $this->notificationService->EliminarNotification($notification_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminarNotifications Acción que elimina los notificationes seleccionados en la BD.
     */
    #[RequireAdminPermission(FunctionId::NOTIFICATION, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarNotifications(NotificationIdsRequest $idsDto): JsonResponse
    {
        $ids = (string) $idsDto->ids;

        try {
            $resultado = $this->notificationService->EliminarNotifications($ids);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * leer Acción para leer las notificaciones.
     */
    #[RequireAdminPermission(FunctionId::NOTIFICATION, AdminPermission::Edit, jsonOnDenied: true)]
    public function leer(): JsonResponse
    {
        try {
            $resultado = $this->notificationService->LeerNotificaciones();
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }
}
