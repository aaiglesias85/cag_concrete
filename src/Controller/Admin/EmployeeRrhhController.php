<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Employee\EmployeeIdRequest;
use App\Dto\Admin\Employee\EmployeeIdsRequest;
use App\Dto\Admin\EmployeeRrhh\EmployeeRrhhActualizarRequest;
use App\Dto\Admin\EmployeeRrhh\EmployeeRrhhListarRequest;
use App\Dto\Admin\EmployeeRrhh\EmployeeRrhhSalvarRequest;
use App\Entity\Race;
use App\Repository\RaceRepository;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\EmployeeRrhhService;
use Symfony\Component\HttpFoundation\JsonResponse;

class EmployeeRrhhController extends AbstractAdminController
{
    private $employeeService;

    public function __construct(
        AdminAccessService $adminAccess,
        EmployeeRrhhService $employeeService)
    {
        parent::__construct($adminAccess);
        $this->employeeService = $employeeService;
    }

    #[RequireAdminPermission(FunctionId::EMPLOYEE_RRHH)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::EMPLOYEE_RRHH);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso EMPLOYEE_RRHH esperado tras #[RequireAdminPermission].');

        /** @var RaceRepository $raceRepo */
        $raceRepo = $this->employeeService->getDoctrine()->getRepository(Race::class);
        $races = $raceRepo->ListarOrdenados();

        return $this->render('admin/employee-rrhh/index.html.twig', [
            'permiso' => $permiso,
            'races' => $races,
        ]);
    }

    #[RequireAdminPermission(FunctionId::EMPLOYEE_RRHH, AdminPermission::View, jsonOnDenied: true)]
    public function listar(EmployeeRrhhListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $result = $this->employeeService->ListarEmployees($listar);

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

    #[RequireAdminPermission(FunctionId::EMPLOYEE_RRHH, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(EmployeeRrhhSalvarRequest $d): JsonResponse
    {
        try {
            $resultado = $this->employeeService->SalvarEmployee($d);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['employee_id'] = $resultado['employee_id'];

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

    #[RequireAdminPermission(FunctionId::EMPLOYEE_RRHH, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(EmployeeRrhhActualizarRequest $d): JsonResponse
    {
        try {
            $resultado = $this->employeeService->ActualizarEmployee($d);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['employee_id'] = $resultado['employee_id'];

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

    #[RequireAdminPermission(FunctionId::EMPLOYEE_RRHH, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(EmployeeIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->employeeService->Eliminar($dto);
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

    #[RequireAdminPermission(FunctionId::EMPLOYEE_RRHH, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarVarios(EmployeeIdsRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->employeeService->EliminarVarios($dto);
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

    #[RequireAdminPermission(FunctionId::EMPLOYEE_RRHH, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(EmployeeIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->employeeService->CargarDatosEmployee($dto);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['employee'] = $resultado['employee'];

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
