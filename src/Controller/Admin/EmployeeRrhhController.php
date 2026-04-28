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

    #[RequireAdminPermission(FunctionId::EMPLOYEE_RRHH, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(EmployeeRrhhSalvarRequest $d): JsonResponse
    {
        $name = (string) $d->name;
        $address = $d->address;
        $phone = $d->phone;
        $cert_rate_type = $d->cert_rate_type;
        $social_security_number = $d->social_security_number;
        $apprentice_percentage = $d->apprentice_percentage;
        $work_code = $d->work_code;
        $gender = $d->gender;
        $race_id = $d->race_id;
        $date_hired = $d->date_hired;
        $date_terminated = $d->date_terminated;
        $reason_terminated = $d->reason_terminated;
        $time_card_notes = $d->time_card_notes;
        $regular_rate_per_hour = $d->regular_rate_per_hour;
        $overtime_rate_per_hour = $d->overtime_rate_per_hour;
        $special_rate_per_hour = $d->special_rate_per_hour;
        $trade_licenses_info = $d->trade_licenses_info;
        $notes = $d->notes;
        $is_osha_10_certified = $d->is_osha_10_certified;
        $is_veteran = $d->is_veteran;
        $status = (string) $d->status;

        try {
            $resultado = $this->employeeService->SalvarEmployee($name, $address, $phone, $cert_rate_type, $social_security_number, $apprentice_percentage, $work_code, $gender, $race_id, $date_hired, $date_terminated, $reason_terminated, $time_card_notes, $regular_rate_per_hour, $overtime_rate_per_hour, $special_rate_per_hour, $trade_licenses_info, $notes, $is_osha_10_certified, $is_veteran, $status);

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
        $employee_id = (string) $d->employee_id;
        $name = (string) $d->name;
        $address = $d->address;
        $phone = $d->phone;
        $cert_rate_type = $d->cert_rate_type;
        $social_security_number = $d->social_security_number;
        $apprentice_percentage = $d->apprentice_percentage;
        $work_code = $d->work_code;
        $gender = $d->gender;
        $race_id = $d->race_id;
        $date_hired = $d->date_hired;
        $date_terminated = $d->date_terminated;
        $reason_terminated = $d->reason_terminated;
        $time_card_notes = $d->time_card_notes;
        $regular_rate_per_hour = $d->regular_rate_per_hour;
        $overtime_rate_per_hour = $d->overtime_rate_per_hour;
        $special_rate_per_hour = $d->special_rate_per_hour;
        $trade_licenses_info = $d->trade_licenses_info;
        $notes = $d->notes;
        $is_osha_10_certified = $d->is_osha_10_certified;
        $is_veteran = $d->is_veteran;
        $status = (string) $d->status;

        try {
            $resultado = $this->employeeService->ActualizarEmployee($employee_id, $name, $address, $phone, $cert_rate_type, $social_security_number, $apprentice_percentage, $work_code, $gender, $race_id, $date_hired, $date_terminated, $reason_terminated, $time_card_notes, $regular_rate_per_hour, $overtime_rate_per_hour, $special_rate_per_hour, $trade_licenses_info, $notes, $is_osha_10_certified, $is_veteran, $status);

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
        $employee_id = $dto->employee_id;

        try {
            $resultado = $this->employeeService->Eliminar($employee_id);
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
        $ids = (string) $dto->ids;

        try {
            $resultado = $this->employeeService->EliminarVarios($ids);
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
}
