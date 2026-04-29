<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\EmployeeRole\EmployeeRoleActualizarRequest;
use App\Dto\Admin\EmployeeRole\EmployeeRoleIdRequest;
use App\Dto\Admin\EmployeeRole\EmployeeRoleIdsRequest;
use App\Dto\Admin\EmployeeRole\EmployeeRoleListarRequest;
use App\Dto\Admin\EmployeeRole\EmployeeRoleSalvarRequest;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\EmployeeRoleService;
use Symfony\Component\HttpFoundation\JsonResponse;

class EmployeeRoleController extends AbstractAdminController
{
    private $employeeRoleService;

    public function __construct(
        AdminAccessService $adminAccess,
        EmployeeRoleService $employeeRoleService)
    {
        parent::__construct($adminAccess);
        $this->employeeRoleService = $employeeRoleService;
    }

    #[RequireAdminPermission(FunctionId::EMPLOYEE_ROLE)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::EMPLOYEE_ROLE);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso EMPLOYEE_ROLE esperado tras #[RequireAdminPermission].');

        return $this->render('admin/employee-role/index.html.twig', [
            'permiso' => $permiso,
        ]);
    }

    #[RequireAdminPermission(FunctionId::EMPLOYEE_ROLE, AdminPermission::View, jsonOnDenied: true)]
    public function listar(EmployeeRoleListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $result = $this->employeeRoleService->Listar($listar);

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

    #[RequireAdminPermission(FunctionId::EMPLOYEE_ROLE, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(EmployeeRoleSalvarRequest $d): JsonResponse
    {
        try {
            $resultado = $this->employeeRoleService->Salvar($d);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['role_id'] = $resultado['role_id'];
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

    #[RequireAdminPermission(FunctionId::EMPLOYEE_ROLE, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(EmployeeRoleActualizarRequest $d): JsonResponse
    {
        try {
            $resultado = $this->employeeRoleService->Actualizar($d);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['role_id'] = $resultado['role_id'];
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

    #[RequireAdminPermission(FunctionId::EMPLOYEE_ROLE, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(EmployeeRoleIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->employeeRoleService->EliminarRole($dto);
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

    #[RequireAdminPermission(FunctionId::EMPLOYEE_ROLE, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarVarios(EmployeeRoleIdsRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->employeeRoleService->EliminarVarios($dto);
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

    #[RequireAdminPermission(FunctionId::EMPLOYEE_ROLE, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(EmployeeRoleIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->employeeRoleService->CargarDatos($dto);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['role'] = $resultado['role'];

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
