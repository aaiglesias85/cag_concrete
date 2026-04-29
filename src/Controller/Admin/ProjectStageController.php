<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\ProjectStage\ProjectStageActualizarRequest;
use App\Dto\Admin\ProjectStage\ProjectStageIdRequest;
use App\Dto\Admin\ProjectStage\ProjectStageIdsRequest;
use App\Dto\Admin\ProjectStage\ProjectStageListarRequest;
use App\Dto\Admin\ProjectStage\ProjectStageSalvarRequest;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\ProjectStageService;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProjectStageController extends AbstractAdminController
{
    private $projectStageService;

    public function __construct(
        AdminAccessService $adminAccess,
        ProjectStageService $projectStageService)
    {
        parent::__construct($adminAccess);
        $this->projectStageService = $projectStageService;
    }

    #[RequireAdminPermission(FunctionId::PROJECT_STAGE)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::PROJECT_STAGE);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso PROJECT_STAGE esperado tras #[RequireAdminPermission].');

        return $this->render('admin/project-stage/index.html.twig', [
            'permiso' => $permiso,
        ]);
    }

    #[RequireAdminPermission(FunctionId::PROJECT_STAGE, AdminPermission::View, jsonOnDenied: true)]
    public function listar(ProjectStageListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $result = $this->projectStageService->ListarStages($listar);

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

    #[RequireAdminPermission(FunctionId::PROJECT_STAGE, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(ProjectStageSalvarRequest $d): JsonResponse
    {
        try {
            $resultado = $this->projectStageService->SalvarStage($d);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['stage_id'] = $resultado['stage_id'];
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

    #[RequireAdminPermission(FunctionId::PROJECT_STAGE, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(ProjectStageActualizarRequest $d): JsonResponse
    {
        try {
            $resultado = $this->projectStageService->ActualizarStage($d);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['stage_id'] = $resultado['stage_id'];
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

    #[RequireAdminPermission(FunctionId::PROJECT_STAGE, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(ProjectStageIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->projectStageService->EliminarStage($dto);
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

    #[RequireAdminPermission(FunctionId::PROJECT_STAGE, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarStages(ProjectStageIdsRequest $idsDto): JsonResponse
    {
        try {
            $resultado = $this->projectStageService->EliminarStages($idsDto);
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

    #[RequireAdminPermission(FunctionId::PROJECT_STAGE, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(ProjectStageIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->projectStageService->CargarDatosStage($dto);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['stage'] = $resultado['stage'];

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
