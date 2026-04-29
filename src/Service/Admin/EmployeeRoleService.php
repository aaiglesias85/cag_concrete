<?php

namespace App\Service\Admin;

use App\Dto\Admin\EmployeeRole\EmployeeRoleActualizarRequest;
use App\Dto\Admin\EmployeeRole\EmployeeRoleIdRequest;
use App\Dto\Admin\EmployeeRole\EmployeeRoleIdsRequest;
use App\Dto\Admin\EmployeeRole\EmployeeRoleListarRequest;
use App\Dto\Admin\EmployeeRole\EmployeeRoleSalvarRequest;
use App\Entity\Employee;
use App\Entity\EmployeeRole;
use App\Repository\EmployeeRepository;
use App\Repository\EmployeeRoleRepository;
use App\Service\Base\Base;

class EmployeeRoleService extends Base
{
    /**
     * CargarDatos: Carga los datos de un employee role.
     *
     * @author Marcel
     */
    public function CargarDatos(EmployeeRoleIdRequest $dto)
    {
        $resultado = [];
        $arreglo_resultado = [];

        $role_id = $dto->role_id;
        $entity = $this->getDoctrine()->getRepository(EmployeeRole::class)
           ->find($role_id);
        /** @var EmployeeRole $entity */
        if (null != $entity) {
            $arreglo_resultado['description'] = $entity->getDescription();
            $arreglo_resultado['status'] = $entity->getStatus();

            $resultado['success'] = true;
            $resultado['role'] = $arreglo_resultado;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarRole: Elimina un employee role en la BD.
     *
     * @author Marcel
     */
    public function EliminarRole(EmployeeRoleIdRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();
        $role_id = $dto->role_id;

        $entity = $this->getDoctrine()->getRepository(EmployeeRole::class)
           ->find($role_id);
        /** @var EmployeeRole $entity */
        if (null != $entity) {
            // employees
            /** @var EmployeeRepository $employeeRepo */
            $employeeRepo = $this->getDoctrine()->getRepository(Employee::class);
            $employees = $employeeRepo->ListarEmployeesDeRole($role_id);
            if (count($employees) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = 'The employee role could not be deleted, because it is related to a employee';

                return $resultado;
            }

            // eliminar informacion relacionada
            $this->EliminarInformacionRelacionada($role_id);

            $description = $entity->getDescription();

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Employee Role';
            $log_descripcion = "The employee role is deleted: $description";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarVarios: Elimina los employee rolees seleccionados en la BD.
     *
     * @author Marcel
     */
    public function EliminarVarios(EmployeeRoleIdsRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();

        $ids = (string) ($dto->ids ?? '');
        $cant_eliminada = 0;
        $cant_total = 0;
        if ('' != $ids) {
            $ids = explode(',', $ids);
            foreach ($ids as $role_id) {
                if ('' != $role_id) {
                    ++$cant_total;
                    $entity = $this->getDoctrine()->getRepository(EmployeeRole::class)
                       ->find($role_id);
                    /** @var EmployeeRole $entity */
                    if (null != $entity) {
                        // employees
                        /** @var EmployeeRepository $employeeRepo */
                        $employeeRepo = $this->getDoctrine()->getRepository(Employee::class);
                        $employees = $employeeRepo->ListarEmployeesDeRole((int) $role_id);
                        if (0 === count($employees)) {
                            // eliminar informacion relacionada
                            $this->EliminarInformacionRelacionada((int) $role_id);

                            $description = $entity->getDescription();

                            $em->remove($entity);
                            ++$cant_eliminada;

                            // Salvar log
                            $log_operacion = 'Delete';
                            $log_categoria = 'Employee Role';
                            $log_descripcion = "The employee role is deleted: $description";
                            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                        }
                    }
                }
            }
        }
        $em->flush();

        if (0 == $cant_eliminada) {
            $resultado['success'] = false;
            $resultado['error'] = 'The employee rolees could not be deleted, because they are associated with a employee';
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? 'The operation was successful' : 'The operation was successful. But attention, it was not possible to delete all the selected employee rolees because they are associated with a employee';
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * EliminarInformacionRelacionada: Elimina la informacion relacionada con un employee role.
     *
     * @param int $role_id Id
     *
     * @return void
     */
    private function EliminarInformacionRelacionada($role_id)
    {
        // Los prevailing roles se eliminan automáticamente por ON DELETE CASCADE
        // en la tabla project_prevailing_role
    }

    /**
     * Actualizar: Actualiza los datos del employee role en la BD.
     *
     * @author Marcel
     */
    public function Actualizar(EmployeeRoleActualizarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $role_id = $d->role_id;
        $description = (string) $d->description;
        $status = (string) $d->status;

        $entity = $this->getDoctrine()->getRepository(EmployeeRole::class)
           ->find($role_id);
        /** @var EmployeeRole $entity */
        if (null != $entity) {
            // Verificar description
            $role = $this->getDoctrine()->getRepository(EmployeeRole::class)
               ->findOneBy(['description' => $description]);
            if (null != $role && $entity->getRoleId() != $role->getRoleId()) {
                $resultado['success'] = false;
                $resultado['error'] = 'The employee role name is in use, please try entering another one.';

                return $resultado;
            }

            $entity->setDescription($description);
            $entity->setStatus($this->parseBooleanStatus($status));

            $em->flush();

            // Salvar log
            $log_operacion = 'Update';
            $log_categoria = 'Employee Role';
            $log_descripcion = "The employee role is modified: $description";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['role_id'] = $entity->getRoleId();

            return $resultado;
        }
        $resultado['success'] = false;
        $resultado['error'] = 'The requested record does not exist';

        return $resultado;
    }

    /**
     * Salvar: Guarda los datos de employee role en la BD.
     *
     * @author Marcel
     */
    public function Salvar(EmployeeRoleSalvarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $description = (string) $d->description;
        $status = (string) $d->status;

        // Verificar description
        $role = $this->getDoctrine()->getRepository(EmployeeRole::class)
           ->findOneBy(['description' => $description]);
        if (null != $role) {
            $resultado['success'] = false;
            $resultado['error'] = 'The employee role description is in use, please try entering another one.';

            return $resultado;
        }

        $entity = new EmployeeRole();

        $entity->setDescription($description);
        $entity->setStatus($this->parseBooleanStatus($status));

        $em->persist($entity);

        $em->flush();

        // Salvar log
        $log_operacion = 'Add';
        $log_categoria = 'Employee Role';
        $log_descripcion = "The employee role is added: $description";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['role_id'] = $entity->getRoleId();

        return $resultado;
    }

    /**
     * Listar: Listar los employee rolees.
     *
     * @author Marcel
     */
    public function Listar(EmployeeRoleListarRequest $listar)
    {
        $dt = $listar->dt;

        /** @var EmployeeRoleRepository $employeeRoleRepo */
        $employeeRoleRepo = $this->getDoctrine()->getRepository(EmployeeRole::class);
        $resultado = $employeeRoleRepo->ListarEmployeeRolesConTotal(
            $dt['start'],
            $dt['length'],
            $dt['search'],
            $dt['orderField'],
            $dt['orderDir']
        );

        $data = [];

        foreach ($resultado['data'] as $value) {
            $role_id = $value->getRoleId();

            $data[] = [
                'id' => $role_id,
                'description' => $value->getDescription(),
                'status' => $value->getStatus() ? 1 : 0,
            ];
        }

        return [
            'data' => $data,
            'total' => $resultado['total'], // ya viene con el filtro aplicado
        ];
    }

    private function parseBooleanStatus(string $status): bool
    {
        return filter_var($status, FILTER_VALIDATE_BOOLEAN);
    }
}
