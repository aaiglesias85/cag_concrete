<?php

namespace App\Utils\Admin;

use App\Entity\EmployeeRole;
use App\Entity\Employee;
use App\Repository\EmployeeRoleRepository;
use App\Repository\EmployeeRepository;
use App\Repository\ProjectRepository;
use App\Entity\Project;

use App\Utils\Base;

class EmployeeRoleService extends Base
{

   /**
    * CargarDatos: Carga los datos de un employee role
    *
    * @param int $role_id Id
    *
    * @author Marcel
    */
   public function CargarDatos($role_id)
   {
      $resultado = array();
      $arreglo_resultado = array();

      $entity = $this->getDoctrine()->getRepository(EmployeeRole::class)
         ->find($role_id);
      /** @var EmployeeRole $entity */
      if ($entity != null) {

         $arreglo_resultado['description'] = $entity->getDescription();
         $arreglo_resultado['status'] = $entity->getStatus();

         $resultado['success'] = true;
         $resultado['role'] = $arreglo_resultado;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * EliminarRole: Elimina un employee role en la BD
    * @param int $role_id Id
    * @author Marcel
    */
   public function EliminarRole($role_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(EmployeeRole::class)
         ->find($role_id);
      /**@var EmployeeRole $entity */
      if ($entity != null) {

         // employees
         /** @var EmployeeRepository $employeeRepo */
         $employeeRepo = $this->getDoctrine()->getRepository(Employee::class);
         $employees = $employeeRepo->ListarEmployeesDeRole($role_id);
         if (count($employees) > 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The employee role could not be deleted, because it is related to a employee";
            return $resultado;
         }

         // eliminar informacion relacionada
         $this->EliminarInformacionRelacionada($role_id);

         $description = $entity->getDescription();

         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Employee Role";
         $log_descripcion = "The employee role is deleted: $description";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * EliminarVarios: Elimina los employee rolees seleccionados en la BD
    * @param int $ids Ids
    * @author Marcel
    */
   public function EliminarVarios($ids)
   {
      $em = $this->getDoctrine()->getManager();

      if ($ids != "") {
         $ids = explode(',', $ids);
         $cant_eliminada = 0;
         $cant_total = 0;
         foreach ($ids as $role_id) {
            if ($role_id != "") {
               $cant_total++;
               $entity = $this->getDoctrine()->getRepository(EmployeeRole::class)
                  ->find($role_id);
               /**@var EmployeeRole $entity */
               if ($entity != null) {

                  // employees
                  /** @var EmployeeRepository $employeeRepo */
                  $employeeRepo = $this->getDoctrine()->getRepository(Employee::class);
                  $employees = $employeeRepo->ListarEmployeesDeRole($role_id);
                  if (count($employees) === 0) {

                     // eliminar informacion relacionada
                     $this->EliminarInformacionRelacionada($role_id);

                     $description = $entity->getDescription();

                     $em->remove($entity);
                     $cant_eliminada++;

                     //Salvar log
                     $log_operacion = "Delete";
                     $log_categoria = "Employee Role";
                     $log_descripcion = "The employee role is deleted: $description";
                     $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                  }
               }
            }
         }
      }
      $em->flush();

      if ($cant_eliminada == 0) {
         $resultado['success'] = false;
         $resultado['error'] = "The employee rolees could not be deleted, because they are associated with a employee";
      } else {
         $resultado['success'] = true;

         $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected employee rolees because they are associated with a employee";
         $resultado['message'] = $mensaje;
      }

      return $resultado;
   }

   /**
    * EliminarInformacionRelacionada: Elimina la informacion relacionada con un employee role
    * @param int $role_id Id
    * @return void
    */
   private function EliminarInformacionRelacionada($role_id)
   {

      // projects prevailing role
      /** @var ProjectRepository $projectRepository */
      $projectRepository = $this->getDoctrine()->getRepository(Project::class);
      $projects = $projectRepository->ListarProjectsDePrevailingRole($role_id);
      foreach ($projects as $project) {
         $project->setPrevailingRole(null);
      }
   }

   /**
    * Actualizar: Actualiza los datos del employee role en la BD
    * @param int $role_id Id
    * @author Marcel
    */
   public function Actualizar($role_id, $description, $status)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(EmployeeRole::class)
         ->find($role_id);
      /** @var EmployeeRole $entity */
      if ($entity != null) {
         //Verificar description
         $role = $this->getDoctrine()->getRepository(EmployeeRole::class)
            ->findOneBy(['description' => $description]);
         if ($role != null && $entity->getRoleId() != $role->getRoleId()) {
            $resultado['success'] = false;
            $resultado['error'] = "The employee role name is in use, please try entering another one.";
            return $resultado;
         }

         $entity->setDescription($description);
         $entity->setStatus($status);

         $em->flush();

         //Salvar log
         $log_operacion = "Update";
         $log_categoria = "Employee Role";
         $log_descripcion = "The employee role is modified: $description";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
         $resultado['role_id'] = $entity->getRoleId();

         return $resultado;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
         return $resultado;
      }
   }

   /**
    * Salvar: Guarda los datos de employee role en la BD
    * @param string $description Description
    * @author Marcel
    */
   public function Salvar($description, $status)
   {
      $em = $this->getDoctrine()->getManager();

      //Verificar description
      $role = $this->getDoctrine()->getRepository(EmployeeRole::class)
         ->findOneBy(['description' => $description]);
      if ($role != null) {
         $resultado['success'] = false;
         $resultado['error'] = "The employee role description is in use, please try entering another one.";
         return $resultado;
      }

      $entity = new EmployeeRole();

      $entity->setDescription($description);
      $entity->setStatus($status);

      $em->persist($entity);

      $em->flush();

      //Salvar log
      $log_operacion = "Add";
      $log_categoria = "Employee Role";
      $log_descripcion = "The employee role is added: $description";
      $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

      $resultado['success'] = true;
      $resultado['role_id'] = $entity->getRoleId();

      return $resultado;
   }

   /**
    * Listar: Listar los employee rolees
    *
    * @param int $start Inicio
    * @param int $limit Limite
    * @param string $sSearch Para buscar
    *
    * @author Marcel
    */
   public function Listar($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
   {
      /** @var EmployeeRoleRepository $employeeRoleRepo */
      $employeeRoleRepo = $this->getDoctrine()->getRepository(EmployeeRole::class);
      $resultado = $employeeRoleRepo->ListarEmployeeRolesConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

      $data = [];

      foreach ($resultado['data'] as $value) {
         $role_id = $value->getRoleId();

         $data[] = array(
            "id" => $role_id,
            "description" => $value->getDescription(),
            "status" => $value->getStatus() ? 1 : 0,
         );
      }

      return [
         'data' => $data,
         'total' => $resultado['total'], // ya viene con el filtro aplicado
      ];
   }
}
