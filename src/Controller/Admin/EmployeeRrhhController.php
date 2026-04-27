<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Controller\Admin\Traits\AdminValidationResponseTrait;
use App\Dto\Admin\Employee\EmployeeIdRequest;
use App\Dto\Admin\Employee\EmployeeIdsRequest;
use App\Dto\Admin\EmployeeRrhh\EmployeeRrhhSalvarRequest;
use App\Entity\Race;
use App\Http\DataTablesHelper;
use App\Repository\RaceRepository;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\EmployeeRrhhService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmployeeRrhhController extends AbstractAdminController
{
    use AdminValidationResponseTrait;

    private $employeeService;

    public function __construct(
        AdminAccessService $adminAccess,
        EmployeeRrhhService $employeeService,
        private ValidatorInterface $validator,
        private TranslatorInterface $adminTranslator,
    ) {
        parent::__construct($adminAccess);
        $this->employeeService = $employeeService;
    }

    public function index()
    {
        $acceso = $this->adminAccess->exigirUsuarioYPermisoVer($this->getUser(), FunctionId::EMPLOYEE_RRHH);
        if ($acceso instanceof RedirectResponse) {
            return $acceso;
        }
        $permiso = $acceso['permisos'];

        // races
        /** @var RaceRepository $raceRepo */
        $raceRepo = $this->employeeService->getDoctrine()->getRepository(Race::class);
        $races = $raceRepo->ListarOrdenados();

        return $this->render('admin/employee-rrhh/index.html.twig', [
            'permiso' => $permiso[0],
            'races' => $races,
        ]);
    }

    /**
     * listar Acción que lista los units.
     */
    public function listar(Request $request)
    {
        try {
            // parsear los parametros de la tabla
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: ['id', 'socialSecurityNumber', 'name', 'address', 'phone', 'gender', 'race', 'status'],
                defaultOrderField: 'name'
            );

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
     * salvar Acción que inserta un menu en la BD.
     */
    public function salvar(Request $request)
    {
        $d = EmployeeRrhhSalvarRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $employee_id = (string) ($d->employee_id ?? '');
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
            if ('' === $employee_id) {
                $resultado = $this->employeeService->SalvarEmployee($name, $address, $phone, $cert_rate_type, $social_security_number, $apprentice_percentage, $work_code, $gender, $race_id, $date_hired, $date_terminated, $reason_terminated, $time_card_notes, $regular_rate_per_hour, $overtime_rate_per_hour, $special_rate_per_hour, $trade_licenses_info, $notes, $is_osha_10_certified, $is_veteran, $status);
            } else {
                $resultado = $this->employeeService->ActualizarEmployee($employee_id, $name, $address, $phone, $cert_rate_type, $social_security_number, $apprentice_percentage, $work_code, $gender, $race_id, $date_hired, $date_terminated, $reason_terminated, $time_card_notes, $regular_rate_per_hour, $overtime_rate_per_hour, $special_rate_per_hour, $trade_licenses_info, $notes, $is_osha_10_certified, $is_veteran, $status);
            }

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
    public function eliminar(Request $request)
    {
        $dto = EmployeeIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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

    /**
     * eliminarVarios Acción que elimina los employees seleccionados en la BD.
     */
    public function eliminarVarios(Request $request)
    {
        $dto = EmployeeIdsRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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

    /**
     * cargarDatos Acción que carga los datos del employee en la BD.
     */
    public function cargarDatos(Request $request)
    {
        $dto = EmployeeIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
