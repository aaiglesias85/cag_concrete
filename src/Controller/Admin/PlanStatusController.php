<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\PlanStatus\PlanStatusActualizarRequest;
use App\Dto\Admin\PlanStatus\PlanStatusIdRequest;
use App\Dto\Admin\PlanStatus\PlanStatusIdsRequest;
use App\Dto\Admin\PlanStatus\PlanStatusListarRequest;
use App\Dto\Admin\PlanStatus\PlanStatusSalvarRequest;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\PlanStatusService;
use Symfony\Component\HttpFoundation\JsonResponse;

class PlanStatusController extends AbstractAdminController
{
    private $planStatusService;

    public function __construct(
        AdminAccessService $adminAccess,
        PlanStatusService $planStatusService)
    {
        parent::__construct($adminAccess);
        $this->planStatusService = $planStatusService;
    }

    #[RequireAdminPermission(FunctionId::PLAN_STATUS)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::PLAN_STATUS);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso PLAN_STATUS esperado tras #[RequireAdminPermission].');

        return $this->render('admin/plan-status/index.html.twig', [
            'permiso' => $permiso,
        ]);
    }

    /**
     * listar Acción que lista los units.
     */
    #[RequireAdminPermission(FunctionId::PLAN_STATUS, AdminPermission::View, jsonOnDenied: true)]
    public function listar(PlanStatusListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $result = $this->planStatusService->ListarStatus($listar);

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
     * salvar Acción para agregar statuss en la BD.
     */
    #[RequireAdminPermission(FunctionId::PLAN_STATUS, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(PlanStatusSalvarRequest $d): JsonResponse
    {
        try {
            $resultado = $this->planStatusService->SalvarStatus($d);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['status_id'] = $resultado['status_id'];
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
     * actualizar Acción para modificar un status en la BD.
     */
    #[RequireAdminPermission(FunctionId::PLAN_STATUS, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(PlanStatusActualizarRequest $d): JsonResponse
    {
        try {
            $resultado = $this->planStatusService->ActualizarStatus($d);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['status_id'] = $resultado['status_id'];
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
     * eliminar Acción que elimina un status en la BD.
     */
    #[RequireAdminPermission(FunctionId::PLAN_STATUS, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(PlanStatusIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->planStatusService->EliminarStatus($dto);
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
     * eliminarStatuss Acción que elimina los statuss seleccionados en la BD.
     */
    #[RequireAdminPermission(FunctionId::PLAN_STATUS, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarStatuss(PlanStatusIdsRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->planStatusService->EliminarStatuss($dto);
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
     * cargarDatos Acción que carga los datos del status en la BD.
     */
    #[RequireAdminPermission(FunctionId::PLAN_STATUS, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(PlanStatusIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->planStatusService->CargarDatosStatus($dto);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['status'] = $resultado['status'];

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
