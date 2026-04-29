<?php

namespace App\Service\Admin;

use App\Dto\Admin\Employee\EmployeeIdRequest;
use App\Dto\Admin\Employee\EmployeeIdsRequest;
use App\Dto\Admin\EmployeeRrhh\EmployeeRrhhActualizarRequest;
use App\Dto\Admin\EmployeeRrhh\EmployeeRrhhListarRequest;
use App\Dto\Admin\EmployeeRrhh\EmployeeRrhhSalvarRequest;
use App\Entity\DataTrackingLabor;
use App\Entity\Employee;
use App\Entity\Race;
use App\Entity\ScheduleEmployee;
use App\Repository\DataTrackingLaborRepository;
use App\Repository\EmployeeRepository;
use App\Repository\ScheduleEmployeeRepository;
use App\Service\Base\Base;

class EmployeeRrhhService extends Base
{
    /**
     * CargarDatosEmployee: Carga los datos de un employee.
     *
     * @author Marcel
     */
    public function CargarDatosEmployee(EmployeeIdRequest $dto)
    {
        $resultado = [];
        $arreglo_resultado = [];

        $employee_id = $dto->employee_id;
        $entity = $this->getDoctrine()->getRepository(Employee::class)
           ->find($employee_id);
        /** @var Employee $entity */
        if (null != $entity) {
            $arreglo_resultado['name'] = $entity->getName();
            $arreglo_resultado['address'] = $entity->getAddress();
            $arreglo_resultado['phone'] = $entity->getPhone();
            $arreglo_resultado['cert_rate_type'] = $entity->getCertRateType();
            $arreglo_resultado['social_security_number'] = $entity->getSocialSecurityNumber();
            $arreglo_resultado['apprentice_percentage'] = $entity->getApprenticePercentage();
            $arreglo_resultado['work_code'] = $entity->getWorkCode();
            $arreglo_resultado['gender'] = $entity->getGender();
            $arreglo_resultado['race'] = $entity->getRace() ? $entity->getRace()->getDescription() : '';
            $arreglo_resultado['date_hired'] = $entity->getDateHired() ? $entity->getDateHired()->format('m/d/Y') : '';
            $arreglo_resultado['date_terminated'] = $entity->getDateTerminated() ? $entity->getDateTerminated()->format('m/d/Y') : '';
            $arreglo_resultado['reason_terminated'] = $entity->getReasonTerminated();
            $arreglo_resultado['time_card_notes'] = $entity->getTimeCardNotes();
            $arreglo_resultado['regular_rate_per_hour'] = $entity->getRegularRatePerHour();
            $arreglo_resultado['overtime_rate_per_hour'] = $entity->getOvertimeRatePerHour();
            $arreglo_resultado['special_rate_per_hour'] = $entity->getSpecialRatePerHour();
            $arreglo_resultado['trade_licenses_info'] = $entity->getTradeLicensesInfo();
            $arreglo_resultado['notes'] = $entity->getNotes();
            $arreglo_resultado['is_osha_10_certified'] = $entity->getIsOsha10Certified();
            $arreglo_resultado['is_veteran'] = $entity->getIsVeteran();
            $arreglo_resultado['status'] = $entity->getStatus() ? 1 : 0;

            $resultado['success'] = true;
            $resultado['employee'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * Eliminar: Elimina un employee en la BD.
     *
     * @author Marcel
     */
    public function Eliminar(EmployeeIdRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();
        $employee_id = $dto->employee_id;

        $entity = $this->getDoctrine()->getRepository(Employee::class)
           ->find($employee_id);
        /** @var Employee $entity */
        if (null != $entity) {
            // eliminar informacion relacionada
            $this->EliminarInformacionRelacionada($employee_id);

            $employee_descripcion = $entity->getName();

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Employee';
            $log_descripcion = "The employee is deleted: $employee_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarVarios: Elimina los employees seleccionados en la BD.
     *
     * @author Marcel
     */
    public function EliminarVarios(EmployeeIdsRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();

        $ids = (string) ($dto->ids ?? '');
        $cant_eliminada = 0;
        $cant_total = 0;
        if ('' != $ids) {
            $ids = explode(',', $ids);
            foreach ($ids as $employee_id) {
                if ('' != $employee_id) {
                    ++$cant_total;
                    $entity = $this->getDoctrine()->getRepository(Employee::class)
                       ->find($employee_id);
                    /** @var Employee $entity */
                    if (null != $entity) {
                        // eliminar informacion relacionada
                        $this->EliminarInformacionRelacionada($employee_id);

                        $employee_descripcion = $entity->getName();

                        $em->remove($entity);
                        ++$cant_eliminada;

                        // Salvar log
                        $log_operacion = 'Delete';
                        $log_categoria = 'Employee';
                        $log_descripcion = "The employee is deleted: $employee_descripcion";
                        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                    }
                }
            }
        }
        $em->flush();

        if (0 == $cant_eliminada) {
            $resultado['success'] = false;
            $resultado['error'] = 'The employees could not be deleted, because they are associated with a project';
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? 'The operation was successful' : 'The operation was successful. But attention, it was not possible to delete all the selected employees because they are associated with a project';
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    private function EliminarInformacionRelacionada($employee_id)
    {
        $em = $this->getDoctrine()->getManager();

        // data trackins
        /** @var DataTrackingLaborRepository $dataTrackingLaborRepo */
        $dataTrackingLaborRepo = $this->getDoctrine()->getRepository(DataTrackingLabor::class);
        $data_tracking_labors = $dataTrackingLaborRepo->ListarDataTrackingsDeEmployee($employee_id);
        foreach ($data_tracking_labors as $data_tracking_labor) {
            $em->remove($data_tracking_labor);
        }

        // schedules
        /** @var ScheduleEmployeeRepository $scheduleEmployeeRepo */
        $scheduleEmployeeRepo = $this->getDoctrine()->getRepository(ScheduleEmployee::class);
        $schedule_employees = $scheduleEmployeeRepo->ListarSchedulesDeEmployee($employee_id);
        foreach ($schedule_employees as $schedule_employee) {
            $em->remove($schedule_employee);
        }
    }

    /**
     * ActualizarEmployee: Actuializa los datos del rol en la BD.
     *
     * @author Marcel
     */
    public function ActualizarEmployee(EmployeeRrhhActualizarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $employee_id = $d->employee_id;
        $entity = $this->getDoctrine()->getRepository(Employee::class)
           ->find($employee_id);
        /** @var Employee|null $entity */
        if (null === $entity) {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';

            return $resultado;
        }

        $social_security_number = $d->social_security_number;
        if (null !== $social_security_number && '' !== $social_security_number) {
            $employee = $this->getDoctrine()->getRepository(Employee::class)
               ->findOneBy(['socialSecurityNumber' => $social_security_number]);
            if (null != $employee && $entity->getEmployeeId() != $employee->getEmployeeId()) {
                $resultado['success'] = false;
                $resultado['error'] = 'The social security number is in use, please try entering another one.';

                return $resultado;
            }
        }

        $this->applyCommonRrhhFieldsToEntity($entity, $d);

        $em->flush();

        $name = (string) $d->name;
        $log_operacion = 'Update';
        $log_categoria = 'Employee';
        $log_descripcion = "The employee is modified: $name";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['employee_id'] = $entity->getEmployeeId();

        return $resultado;
    }

    /**
     * SalvarEmployee: Guarda los datos de employee en la BD.
     *
     * @author Marcel
     */
    public function SalvarEmployee(EmployeeRrhhSalvarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $social_security_number = $d->social_security_number;
        if (null !== $social_security_number && '' !== $social_security_number) {
            $employee = $this->getDoctrine()->getRepository(Employee::class)
               ->findOneBy(['socialSecurityNumber' => $social_security_number]);
            if (null != $employee) {
                $resultado['success'] = false;
                $resultado['error'] = 'The social security number is in use, please try entering another one.';

                return $resultado;
            }
        }

        $entity = new Employee();

        $this->applyCommonRrhhFieldsToEntity($entity, $d);

        $em->persist($entity);

        $em->flush();

        $name = (string) $d->name;
        $log_operacion = 'Add';
        $log_categoria = 'Employee';
        $log_descripcion = "The employee is added: $name";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['employee_id'] = $entity->getEmployeeId();

        return $resultado;
    }

    /**
     * ListarEmployees: Listar los employees.
     *
     * @author Marcel
     */
    public function ListarEmployees(EmployeeRrhhListarRequest $listar)
    {
        $dt = $listar->dt;

        /** @var EmployeeRepository $employeeRepo */
        $employeeRepo = $this->getDoctrine()->getRepository(Employee::class);
        $resultado = $employeeRepo->ListarEmployeesConTotalRrhh(
            $dt['start'],
            $dt['length'],
            $dt['search'],
            $dt['orderField'],
            $dt['orderDir']
        );

        $data = [];

        foreach ($resultado['data'] as $value) {
            $employee_id = $value->getEmployeeId();

            $data[] = [
                'id' => $employee_id,
                'name' => $value->getName(),
                'address' => $value->getAddress(),
                'phone' => $value->getPhone() ?? '',
                'certRateType' => $value->getCertRateType(),
                'socialSecurityNumber' => $value->getSocialSecurityNumber(),
                'apprenticePercentage' => $value->getApprenticePercentage(),
                'workCode' => $value->getWorkCode(),
                'race' => $value->getRace() ? $value->getRace()->getDescription() : '',
                'gender' => $value->getGender(),
                'dateHired' => $value->getDateHired() ? $value->getDateHired()->format('m/d/Y') : '',
                'dateTerminated' => $value->getDateTerminated() ? $value->getDateTerminated()->format('m/d/Y') : '',
                'reasonTerminated' => $value->getReasonTerminated(),
                'timeCardNotes' => $value->getTimeCardNotes(),
                'regularRatePerHour' => $value->getRegularRatePerHour(),
                'overtimeRatePerHour' => $value->getOvertimeRatePerHour(),
                'specialRatePerHour' => $value->getSpecialRatePerHour(),
                'tradeLicensesInfo' => $value->getTradeLicensesInfo(),
                'notes' => $value->getNotes(),
                'isOsha10Certified' => $value->getIsOsha10Certified(),
                'isVeteran' => $value->getIsVeteran(),
                'status' => $value->getStatus() ? 1 : 0,
            ];
        }

        return [
            'data' => $data,
            'total' => $resultado['total'], // ya viene con el filtro aplicado
        ];
    }

    private function applyCommonRrhhFieldsToEntity(Employee $entity, EmployeeRrhhSalvarRequest|EmployeeRrhhActualizarRequest $d): void
    {
        $entity->setName((string) $d->name);
        $entity->setAddress($d->address);
        $entity->setPhone($d->phone);
        $entity->setCertRateType($d->cert_rate_type);
        $entity->setSocialSecurityNumber($d->social_security_number);
        $entity->setApprenticePercentage($this->nullableFloat($d->apprentice_percentage));
        $entity->setWorkCode($d->work_code);
        $entity->setGender($d->gender);

        $race_id = $d->race_id;
        if (null !== $race_id && '' !== $race_id) {
            $race = $this->getDoctrine()->getRepository(Race::class)
               ->find($race_id);
            $entity->setRace($race);
        }

        $dh = $d->date_hired;
        if (null !== $dh && '' !== $dh) {
            $entity->setDateHired(\DateTime::createFromFormat('m/d/Y', $dh));
        }

        $dtTerm = $d->date_terminated;
        if (null !== $dtTerm && '' !== $dtTerm) {
            $entity->setDateTerminated(\DateTime::createFromFormat('m/d/Y', $dtTerm));
        }

        $entity->setReasonTerminated($d->reason_terminated);
        $entity->setTimeCardNotes($d->time_card_notes);
        $entity->setRegularRatePerHour($this->nullableFloat($d->regular_rate_per_hour));
        $entity->setOvertimeRatePerHour($this->nullableFloat($d->overtime_rate_per_hour));
        $entity->setSpecialRatePerHour($this->nullableFloat($d->special_rate_per_hour));
        $entity->setTradeLicensesInfo($d->trade_licenses_info);
        $entity->setNotes($d->notes);
        $entity->setIsOsha10Certified($this->optionalBoolFromFormValue($d->is_osha_10_certified));
        $entity->setIsVeteran($this->optionalBoolFromFormValue($d->is_veteran));
        $entity->setStatus($this->parseBooleanStatus((string) $d->status));
    }

    private function nullableFloat(?string $v): ?float
    {
        if (null === $v || '' === $v) {
            return null;
        }

        return (float) $v;
    }

    private function optionalBoolFromFormValue(?string $v): ?bool
    {
        if (null === $v || '' === $v) {
            return null;
        }

        return filter_var($v, FILTER_VALIDATE_BOOLEAN);
    }

    private function parseBooleanStatus(string $status): bool
    {
        return filter_var($status, FILTER_VALIDATE_BOOLEAN);
    }
}
