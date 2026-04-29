<?php

namespace App\Service\Admin;

use App\Dto\Admin\Employee\EmployeeActualizarRequest;
use App\Dto\Admin\Employee\EmployeeIdRequest;
use App\Dto\Admin\Employee\EmployeeIdsRequest;
use App\Dto\Admin\Employee\EmployeeListarRequest;
use App\Dto\Admin\Employee\EmployeeSalvarRequest;
use App\Entity\DataTrackingLabor;
use App\Entity\Employee;
use App\Entity\EmployeeRole;
use App\Entity\ScheduleEmployee;
use App\Repository\DataTrackingLaborRepository;
use App\Repository\EmployeeRepository;
use App\Repository\ScheduleEmployeeRepository;
use App\Service\Base\Base;

class EmployeeService extends Base
{
    /**
     * ListarOrdenados.
     *
     * @return array
     */
    public function ListarOrdenados()
    {
        $employees = [];

        /** @var EmployeeRepository $employeeRepo */
        $employeeRepo = $this->getDoctrine()->getRepository(Employee::class);
        $lista = $employeeRepo->ListarOrdenados();

        foreach ($lista as $value) {
            $employees[] = [
                'employee_id' => $value->getEmployeeId(),
                'name' => $value->getName(),
            ];
        }

        return $employees;
    }

    /**
     * ListarProjects.
     *
     * @return array
     */
    public function ListarProjects(EmployeeIdRequest $dto)
    {
        $employee_id = null !== $dto->employee_id ? (string) $dto->employee_id : null;
        $projects = [];

        /** @var DataTrackingLaborRepository $dataTrackingLaborRepo */
        $dataTrackingLaborRepo = $this->getDoctrine()->getRepository(DataTrackingLabor::class);
        $employee_projects = $dataTrackingLaborRepo->ListarProjectsDeEmployee($employee_id);

        foreach ($employee_projects as $key => $employee_project) {
            $value = $employee_project->getDataTracking()->getProject();
            $project_id = $value->getProjectId();

            // listar ultima nota del proyecto
            $nota = $this->ListarUltimaNotaDeProject($project_id);

            $projects[] = [
                'id' => $project_id,
                'project_id' => $project_id,
                'projectNumber' => $value->getProjectNumber(),
                'number' => $value->getProjectNumber(),
                'name' => $value->getName(),
                'description' => $value->getDescription(),
                'company' => $value->getCompany()->getName(),
                'county' => $this->getCountiesDescriptionForProject($value),
                'status' => $value->getStatus(),
                'startDate' => '' != $value->getStartDate() ? $value->getStartDate()->format('m/d/Y') : '',
                'endDate' => '' != $value->getEndDate() ? $value->getEndDate()->format('m/d/Y') : '',
                'dueDate' => '' != $value->getDueDate() ? $value->getDueDate()->format('m/d/Y') : '',
                'nota' => $nota,
                'posicion' => $key,
            ];
        }

        return $projects;
    }

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
            $arreglo_resultado['hourly_rate'] = $entity->getHourlyRate();
            $arreglo_resultado['role_id'] = $entity->getRole() ? $entity->getRole()->getRoleId() : '';
            $arreglo_resultado['color'] = $entity->getColor();

            $resultado['success'] = true;
            $resultado['employee'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarEmployee: Elimina un employee en la BD.
     *
     * @author Marcel
     */
    public function EliminarEmployee(EmployeeIdRequest $dto)
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
     * EliminarEmployees: Elimina los employees seleccionados en la BD.
     *
     * @author Marcel
     */
    public function EliminarEmployees(EmployeeIdsRequest $dto)
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
    public function ActualizarEmployee(EmployeeActualizarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $employee_id = $d->employee_id;
        $name = (string) $d->name;
        $hourly_rate = $d->hourly_rate;
        $role_id = $d->role_id;
        $color = $d->color;

        $entity = $this->getDoctrine()->getRepository(Employee::class)
           ->find($employee_id);
        /** @var Employee $entity */
        if (null != $entity) {
            $entity->setName($name);
            $entity->setHourlyRate(null !== $hourly_rate && '' !== $hourly_rate ? (float) $hourly_rate : null);

            if (null !== $role_id && '' !== $role_id) {
                $role = $this->getDoctrine()->getRepository(EmployeeRole::class)
                   ->find($role_id);
                $entity->setRole($role);
            } else {
                $entity->setRole(null);
            }

            $entity->setColor($color);

            $em->flush();

            // Salvar log
            $log_operacion = 'Update';
            $log_categoria = 'Employee';
            $log_descripcion = "The employee is modified: $name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['employee_id'] = $entity->getEmployeeId();

            return $resultado;
        }

        $resultado['success'] = false;
        $resultado['error'] = 'The requested record does not exist';

        return $resultado;
    }

    /**
     * SalvarEmployee: Guarda los datos de employee en la BD.
     *
     * @author Marcel
     */
    public function SalvarEmployee(EmployeeSalvarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $name = (string) $d->name;
        $hourly_rate = $d->hourly_rate;
        $role_id = $d->role_id;
        $color = $d->color;

        $existe = $this->getDoctrine()->getRepository(Employee::class)->findOneBy(['name' => $name]);

        if ($existe) {
            return [
                'success' => false,
                'error' => 'An employee with this name already exists '.$name,
            ];
        }

        $entity = new Employee();

        $entity->setName($name);
        $entity->setStatus(true);
        $entity->setHourlyRate(null !== $hourly_rate && '' !== $hourly_rate ? (float) $hourly_rate : null);

        if (null !== $role_id && '' !== $role_id) {
            $role = $this->getDoctrine()->getRepository(EmployeeRole::class)
               ->find($role_id);
            $entity->setRole($role);
        }

        $entity->setColor($color);

        $em->persist($entity);

        $em->flush();

        // Salvar log
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
    public function ListarEmployees(EmployeeListarRequest $listar)
    {
        $dt = $listar->dt;

        /** @var EmployeeRepository $employeeRepo */
        $employeeRepo = $this->getDoctrine()->getRepository(Employee::class);
        $resultado = $employeeRepo->ListarEmployeesConTotal(
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
                'hourlyRate' => $value->getHourlyRate(),
                'position' => $value->getRole() ? $value->getRole()->getDescription() : '',
                'color' => $value->getColor(),
            ];
        }

        return [
            'data' => $data,
            'total' => $resultado['total'], // ya viene con el filtro aplicado
        ];
    }
}
