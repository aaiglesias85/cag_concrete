<?php

namespace App\Utils\Admin;

use App\Entity\DataTrackingLabor;
use App\Entity\Employee;
use App\Entity\Schedule;
use App\Entity\ScheduleEmployee;
use App\Utils\Base;

class EmployeeService extends Base
{

    /**
     * ListarOrdenados
     * @return array
     */
    public function ListarOrdenados()
    {
        $employees = [];

        $lista = $this->getDoctrine()->getRepository(Employee::class)
            ->ListarOrdenados();

        foreach ($lista as $value) {
            $employees[] = [
                'employee_id' => $value->getEmployeeId(),
                'name' => $value->getName(),
            ];

        }

        return $employees;
    }

    /**
     * ListarProjects
     * @param $employee_id
     * @return array
     */
    public function ListarProjects($employee_id)
    {
        $projects = [];

        $employee_projects = $this->getDoctrine()->getRepository(DataTrackingLabor::class)
            ->ListarProjectsDeEmployee($employee_id);

        foreach ($employee_projects as $key => $employee_project) {
            $value = $employee_project->getDataTracking()->getProject();
            $project_id = $value->getProjectId();

            // listar ultima nota del proyecto
            $nota = $this->ListarUltimaNotaDeProject($project_id);

            $projects[] = [
                "id" => $project_id,
                "project_id" => $project_id,
                "projectNumber" => $value->getProjectNumber(),
                "number" => $value->getProjectNumber(),
                "name" => $value->getName(),
                "description" => $value->getDescription(),
                "company" => $value->getCompany()->getName(),
                "county" => $value->getCountyObj() ? $value->getCountyObj()->getDescription() : "",
                "status" => $value->getStatus(),
                "startDate" => $value->getStartDate() != '' ? $value->getStartDate()->format('m/d/Y') : '',
                "endDate" => $value->getEndDate() != '' ? $value->getEndDate()->format('m/d/Y') : '',
                "dueDate" => $value->getDueDate() != '' ? $value->getDueDate()->format('m/d/Y') : '',
                'nota' => $nota,
                'posicion' => $key
            ];
        }

        return $projects;
    }

    /**
     * CargarDatosEmployee: Carga los datos de un employee
     *
     * @param int $employee_id Id
     *
     * @author Marcel
     */
    public function CargarDatosEmployee($employee_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(Employee::class)
            ->find($employee_id);
        /** @var Employee $entity */
        if ($entity != null) {

            $arreglo_resultado['name'] = $entity->getName();
            $arreglo_resultado['hourly_rate'] = $entity->getHourlyRate();
            $arreglo_resultado['position'] = $entity->getPosition();
            $arreglo_resultado['color'] = $entity->getColor();

            $resultado['success'] = true;
            $resultado['employee'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarEmployee: Elimina un employee en la BD
     * @param int $employee_id Id
     * @author Marcel
     */
    public function EliminarEmployee($employee_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Employee::class)
            ->find($employee_id);
        /**@var Employee $entity */
        if ($entity != null) {

            // eliminar informacion relacionada
            $this->EliminarInformacionRelacionada($employee_id);

            $employee_descripcion = $entity->getName();


            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Employee";
            $log_descripcion = "The employee is deleted: $employee_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarEmployees: Elimina los employees seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarEmployees($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            $cant_eliminada = 0;
            $cant_total = 0;
            foreach ($ids as $employee_id) {
                if ($employee_id != "") {
                    $cant_total++;
                    $entity = $this->getDoctrine()->getRepository(Employee::class)
                        ->find($employee_id);
                    /**@var Employee $entity */
                    if ($entity != null) {

                        // eliminar informacion relacionada
                        $this->EliminarInformacionRelacionada($employee_id);

                        $employee_descripcion = $entity->getName();

                        $em->remove($entity);
                        $cant_eliminada++;

                        //Salvar log
                        $log_operacion = "Delete";
                        $log_categoria = "Employee";
                        $log_descripcion = "The employee is deleted: $employee_descripcion";
                        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

                    }
                }
            }
        }
        $em->flush();

        if ($cant_eliminada == 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The employees could not be deleted, because they are associated with a project";
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected employees because they are associated with a project";
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    private function EliminarInformacionRelacionada($employee_id)
    {
        $em = $this->getDoctrine()->getManager();

        // data trackins
        $data_tracking_labors = $this->getDoctrine()->getRepository(DataTrackingLabor::class)
            ->ListarDataTrackingsDeEmployee($employee_id);
        foreach ($data_tracking_labors as $data_tracking_labor) {
            $em->remove($data_tracking_labor);
        }

        // schedules
        $schedule_employees = $this->getDoctrine()->getRepository(ScheduleEmployee::class)
            ->ListarSchedulesDeEmployee($employee_id);
        foreach ($schedule_employees as $schedule_employee) {
            $em->remove($schedule_employee);
        }
    }

    /**
     * ActualizarEmployee: Actuializa los datos del rol en la BD
     * @param int $employee_id Id
     * @author Marcel
     */
    public function ActualizarEmployee($employee_id, $name, $hourly_rate, $position, $color)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Employee::class)
            ->find($employee_id);
        /** @var Employee $entity */
        if ($entity != null) {
            //Verificar description
            $employee = $this->getDoctrine()->getRepository(Employee::class)
                ->findOneBy(['name' => $name]);
            if ($employee != null && $entity->getEmployeeId() != $employee->getEmployeeId()) {
                $resultado['success'] = false;
                $resultado['error'] = "The employee name is in use, please try entering another one.";
                return $resultado;
            }

            $entity->setName($name);
            $entity->setHourlyRate($hourly_rate);
            $entity->setPosition($position);
            $entity->setColor($color);

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "Employee";
            $log_descripcion = "The employee is modified: $name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['employee_id'] = $entity->getEmployeeId();

            return $resultado;
        }
    }

    /**
     * SalvarEmployee: Guarda los datos de employee en la BD
     * @param string $description Nombre
     * @author Marcel
     */
    public function SalvarEmployee($name, $hourly_rate, $position, $color)
    {
        $em = $this->getDoctrine()->getManager();

        //Verificar name
        $employee = $this->getDoctrine()->getRepository(Employee::class)
            ->findOneBy(['name' => $name]);
        if ($employee != null ) {
            $resultado['success'] = false;
            $resultado['error'] = "The employee name is in use, please try entering another one.";
            return $resultado;
        }

        $entity = new Employee();

        $entity->setName($name);
        $entity->setHourlyRate($hourly_rate);
        $entity->setPosition($position);
        $entity->setColor($color);

        $em->persist($entity);

        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "Employee";
        $log_descripcion = "The employee is added: $name";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['employee_id'] = $entity->getEmployeeId();

        return $resultado;
    }

    /**
     * ListarEmployees: Listar los employees
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarEmployees($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $resultado = $this->getDoctrine()->getRepository(Employee::class)
            ->ListarEmployeesConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

        $data = [];

        foreach ($resultado['data'] as $value) {
            $employee_id = $value->getEmployeeId();

            $data[] = array(
                "id" => $employee_id,
                "name" => $value->getName(),
                "hourlyRate" => $value->getHourlyRate(),
                "position" => $value->getPosition(),
                "color" => $value->getColor(),
            );
        }

        return [
            'data' => $data,
            'total' => $resultado['total'], // ya viene con el filtro aplicado
        ];
    }
}