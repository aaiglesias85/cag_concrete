<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Log\LogIdRequest;
use App\Dto\Admin\Log\LogIdsRequest;
use App\Dto\Admin\Log\LogListarRequest;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\LogService;
use Symfony\Component\HttpFoundation\JsonResponse;
class LogController extends AbstractAdminController
{
    private $logService;

    public function __construct(
        AdminAccessService $adminAccess,
        LogService $logService) {
        parent::__construct($adminAccess);
        $this->logService = $logService;
    }

    #[RequireAdminPermission(FunctionId::LOG)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::LOG);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso LOG esperado tras #[RequireAdminPermission].');

        return $this->render('admin/log/index.html.twig', [
            'permiso' => $permiso,
        ]);
    }

    /**
     * listar Acción que lista los usuarios.
     */
    #[RequireAdminPermission(FunctionId::LOG, AdminPermission::View, jsonOnDenied: true)]
    public function listar(LogListarRequest $listar): JsonResponse
    {
        try {
            $usuario = $this->DevolverUsuario();

            $dt = $listar->dt;

            $fecha_inicial = $listar->fecha_inicial;
            $fecha_fin = $listar->fecha_fin;

            $usuario_id = $usuario->isAdministrador() ? '' : $usuario->getUsuarioId();

            // total + data en una sola llamada a tu servicio
            $result = $this->logService->ListarLogs(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $fecha_inicial,
                $fecha_fin,
                $usuario_id);

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
     * eliminar Acción que elimina un log en la BD.
     */
    #[RequireAdminPermission(FunctionId::LOG, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(LogIdRequest $dto): JsonResponse
    {
        $log_id = $dto->log_id;

        $resultado = $this->logService->EliminarLog($log_id);
        if ($resultado['success']) {
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['message'] = 'The operation was successful';

            return $this->json($resultadoJson);
        }
        $resultadoJson['success'] = $resultado['success'];
        $resultadoJson['error'] = $resultado['error'];

        return $this->json($resultadoJson);
    }

    /**
     * eliminarLogs Acción que elimina los loges seleccionados en la BD.
     */
    #[RequireAdminPermission(FunctionId::LOG, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarLogs(LogIdsRequest $idsDto): JsonResponse
    {
        $ids = (string) $idsDto->ids;

        $resultado = $this->logService->EliminarLogs($ids);
        if ($resultado['success']) {
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['message'] = 'The operation was successful';

            return $this->json($resultadoJson);
        }
        $resultadoJson['success'] = $resultado['success'];
        $resultadoJson['error'] = $resultado['error'];

        return $this->json($resultadoJson);
    }
}
