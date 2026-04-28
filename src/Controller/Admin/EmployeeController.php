<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Employee\EmployeeActualizarRequest;
use App\Dto\Admin\Employee\EmployeeIdRequest;
use App\Dto\Admin\Employee\EmployeeIdsRequest;
use App\Dto\Admin\Employee\EmployeeListarRequest;
use App\Dto\Admin\Employee\EmployeeSalvarRequest;
use App\Entity\EmployeeRole;
use App\Entity\Race;
use App\Repository\EmployeeRoleRepository;
use App\Repository\RaceRepository;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\EmployeeService;
use Symfony\Component\HttpFoundation\JsonResponse;
class EmployeeController extends AbstractAdminController
{
    private $employeeService;

    public function __construct(
        AdminAccessService $adminAccess,
        EmployeeService $employeeService) {
        parent::__construct($adminAccess);
        $this->employeeService = $employeeService;
    }

    #[RequireAdminPermission(FunctionId::EMPLOYEE)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::EMPLOYEE);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso EMPLOYEE esperado tras #[RequireAdminPermission].');

        // races
        /** @var RaceRepository $raceRepo */
        $raceRepo = $this->employeeService->getDoctrine()->getRepository(Race::class);
        $races = $raceRepo->ListarOrdenados();

        // employee_roles
        /** @var EmployeeRoleRepository $employeeRoleRepo */
        $employeeRoleRepo = $this->employeeService->getDoctrine()->getRepository(EmployeeRole::class);
        $employee_roles = $employeeRoleRepo->ListarOrdenados();

        return $this->render('admin/employee/index.html.twig', [
            'permiso' => $permiso,
            'races' => $races,
            'employee_roles' => $employee_roles,
        ]);
    }

    /**
     * listar Acción que lista los units.
     */
    #[RequireAdminPermission(FunctionId::EMPLOYEE, AdminPermission::View, jsonOnDenied: true)]
    public function listar(EmployeeListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            // total + data en una sola llamada a tu servicio
            $result = $this->employeeService->ListarEmployees(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir']
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
     * salvar Acción que inserta un employee en la BD.
     */
    #[RequireAdminPermission(FunctionId::EMPLOYEE, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(EmployeeSalvarRequest $d): JsonResponse
    {
        $name = (string) $d->name;
        $hourly_rate = $d->hourly_rate;
        $role_id = $d->role_id;
        $color = $d->color;

        try {
            $resultado = $this->employeeService->SalvarEmployee($name, $hourly_rate, $role_id, $color);

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

    /**
     * actualizar Acción que actualiza un employee en la BD.
     */
    #[RequireAdminPermission(FunctionId::EMPLOYEE, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(EmployeeActualizarRequest $d): JsonResponse
    {
        $employee_id = (string) $d->employee_id;
        $name = (string) $d->name;
        $hourly_rate = $d->hourly_rate;
        $role_id = $d->role_id;
        $color = $d->color;

        try {
            $resultado = $this->employeeService->ActualizarEmployee($employee_id, $name, $hourly_rate, $role_id, $color);

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

    /**
     * eliminar Acción que elimina un employee en la BD.
     */
    #[RequireAdminPermission(FunctionId::EMPLOYEE, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(EmployeeIdRequest $dto): JsonResponse
    {
        $employee_id = $dto->employee_id;

        try {
            $resultado = $this->employeeService->EliminarEmployee($employee_id);
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
     * eliminarEmployees Acción que elimina los employees seleccionados en la BD.
     */
    #[RequireAdminPermission(FunctionId::EMPLOYEE, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarEmployees(EmployeeIdsRequest $dto): JsonResponse
    {
        $ids = (string) $dto->ids;

        try {
            $resultado = $this->employeeService->EliminarEmployees($ids);
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
     * cargarDatos Acción que carga los datos del employee en la BD.
     */
    #[RequireAdminPermission(FunctionId::EMPLOYEE, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(EmployeeIdRequest $dto): JsonResponse
    {
        $employee_id = $dto->employee_id;

        try {
            $resultado = $this->employeeService->CargarDatosEmployee($employee_id);
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

    /**
     * listarProjects Acción que lista los projects de employee.
     */
    #[RequireAdminPermission(FunctionId::EMPLOYEE, AdminPermission::View, jsonOnDenied: true)]
    public function listarProjects(EmployeeIdRequest $dto): JsonResponse
    {
        $employee_id = $dto->employee_id;

        try {
            $projects = $this->employeeService->ListarProjects($employee_id);

            $resultadoJson['success'] = true;
            $resultadoJson['projects'] = $projects;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }
}
