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

            // total + data en una sola llamada a tu servicio
            $result = $this->notificationService->ListarNotifications($listar, $usuario);

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
        try {
            $resultado = $this->notificationService->EliminarNotification($dto);
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
        try {
            $resultado = $this->notificationService->EliminarNotifications($idsDto);
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
