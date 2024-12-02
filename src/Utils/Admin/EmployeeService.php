<?php

namespace App\Utils\Admin;

use App\Entity\DataTrackingLabor;
use App\Entity\Employee;
use App\Utils\Base;

class EmployeeService extends Base
{

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

            // data trackins
            $data_tracking_labors = $this->getDoctrine()->getRepository(DataTrackingLabor::class)
                ->ListarDataTrackingsDeEmployee($employee_id);
            foreach ($data_tracking_labors as $data_tracking_labor) {
                $em->remove($data_tracking_labor);
            }

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

                        // data trackins
                        $data_tracking_labors = $this->getDoctrine()->getRepository(DataTrackingLabor::class)
                            ->ListarDataTrackingsDeEmployee($employee_id);
                        foreach ($data_tracking_labors as $data_tracking_labor) {
                            $em->remove($data_tracking_labor);
                        }

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

    /**
     * ActualizarEmployee: Actuializa los datos del rol en la BD
     * @param int $employee_id Id
     * @author Marcel
     */
    public function ActualizarEmployee($employee_id, $name, $hourly_rate, $position)
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

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "Employee";
            $log_descripcion = "The employee is modified: $name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

            return $resultado;
        }
    }

    /**
     * SalvarEmployee: Guarda los datos de employee en la BD
     * @param string $description Nombre
     * @author Marcel
     */
    public function SalvarEmployee($name, $hourly_rate, $position)
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

        $em->persist($entity);

        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "Employee";
        $log_descripcion = "The employee is added: $name";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;

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
        $arreglo_resultado = array();
        $cont = 0;

        $lista = $this->getDoctrine()->getRepository(Employee::class)
            ->ListarEmployees($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

        foreach ($lista as $value) {
            $employee_id = $value->getEmployeeId();

            $acciones = $this->ListarAcciones($employee_id);

            $arreglo_resultado[$cont] = array(
                "id" => $employee_id,
                "name" => $value->getName(),
                "hourlyRate" => $value->getHourlyRate(),
                "position" => $value->getPosition(),
                "acciones" => $acciones
            );

            $cont++;
        }

        return $arreglo_resultado;
    }

    /**
     * TotalEmployees: Total de employees
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalEmployees($sSearch)
    {
        $total = $this->getDoctrine()->getRepository(Employee::class)
            ->TotalEmployees($sSearch);

        return $total;
    }

    /**
     * ListarAcciones: Lista los permisos de un usuario de la BD
     *
     * @author Marcel
     */
    public function ListarAcciones($id)
    {
        $usuario = $this->getUser();
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 14);

        $acciones = "";

        if (count($permiso) > 0) {
            if ($permiso[0]['editar']) {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="Edit record" data-id="' . $id . '"> <i class="la la-edit"></i> </a> ';
            } else {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="View record" data-id="' . $id . '"> <i class="la la-eye"></i> </a> ';
            }
            if ($permiso[0]['eliminar']) {
                $acciones .= ' <a href="javascript:;" class="delete m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete record" data-id="' . $id . '"><i class="la la-trash"></i></a>';
            }
        }

        return $acciones;
    }
}