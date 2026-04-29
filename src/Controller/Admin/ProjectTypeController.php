<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\ProjectType\ProjectTypeActualizarRequest;
use App\Dto\Admin\ProjectType\ProjectTypeIdRequest;
use App\Dto\Admin\ProjectType\ProjectTypeIdsRequest;
use App\Dto\Admin\ProjectType\ProjectTypeListarRequest;
use App\Dto\Admin\ProjectType\ProjectTypeSalvarRequest;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\ProjectTypeService;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProjectTypeController extends AbstractAdminController
{
    private $projectTypeService;

    public function __construct(
        AdminAccessService $adminAccess,
        ProjectTypeService $projectTypeService)
    {
        parent::__construct($adminAccess);
        $this->projectTypeService = $projectTypeService;
    }

    #[RequireAdminPermission(FunctionId::PROJECT_TYPE)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::PROJECT_TYPE);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso PROJECT_TYPE esperado tras #[RequireAdminPermission].');

        return $this->render('admin/project-type/index.html.twig', [
            'permiso' => $permiso,
        ]);
    }

    #[RequireAdminPermission(FunctionId::PROJECT_TYPE, AdminPermission::View, jsonOnDenied: true)]
    public function listar(ProjectTypeListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $result = $this->projectTypeService->ListarTypes($listar);

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

    #[RequireAdminPermission(FunctionId::PROJECT_TYPE, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(ProjectTypeSalvarRequest $d): JsonResponse
    {
        try {
            $resultado = $this->projectTypeService->SalvarType($d);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['type_id'] = $resultado['type_id'];
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

    #[RequireAdminPermission(FunctionId::PROJECT_TYPE, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(ProjectTypeActualizarRequest $d): JsonResponse
    {
        try {
            $resultado = $this->projectTypeService->ActualizarType($d);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['type_id'] = $resultado['type_id'];
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

    #[RequireAdminPermission(FunctionId::PROJECT_TYPE, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(ProjectTypeIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->projectTypeService->EliminarType($dto);
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

    #[RequireAdminPermission(FunctionId::PROJECT_TYPE, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarTypes(ProjectTypeIdsRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->projectTypeService->EliminarTypes($dto);
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

    #[RequireAdminPermission(FunctionId::PROJECT_TYPE, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(ProjectTypeIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->projectTypeService->CargarDatosType($dto);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['type'] = $resultado['type'];

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
