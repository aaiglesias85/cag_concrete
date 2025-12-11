<?php

namespace App\Utils\Admin;

use App\Entity\DataTrackingLabor;
use App\Entity\DataTrackingSubcontract;
use App\Entity\Project;
use App\Entity\Subcontractor;
use App\Entity\SubcontractorEmployee;
use App\Entity\SubcontractorNotes;
use App\Repository\DataTrackingLaborRepository;
use App\Repository\DataTrackingSubcontractRepository;
use App\Repository\ProjectRepository;
use App\Repository\SubcontractorEmployeeRepository;
use App\Repository\SubcontractorNotesRepository;
use App\Repository\SubcontractorRepository;
use App\Utils\Base;

class SubcontractorService extends Base
{

   /**
    * ListarOrdenados
    * @return array
    */
   public function ListarOrdenados()
   {
      $subcontractors = [];

      /** @var SubcontractorRepository $subcontractorRepo */
      $subcontractorRepo = $this->getDoctrine()->getRepository(Subcontractor::class);
      $lista = $subcontractorRepo->ListarOrdenados();

      foreach ($lista as $value) {
         $subcontractors[] = [
            'subcontractor_id' => $value->getSubcontractorId(),
            'name' => $value->getName(),
         ];
      }

      return $subcontractors;
   }

   /**
    * ListarEmployeesDeSubcontractor
    * @param $subcontractor_id
    * @return []
    */
   public function ListarEmployeesDeSubcontractor($subcontractor_id)
   {
      $arreglo_resultado = [];

      /** @var SubcontractorEmployeeRepository $subcontractorEmployeeRepo */
      $subcontractorEmployeeRepo = $this->getDoctrine()->getRepository(SubcontractorEmployee::class);
      $employees = $subcontractorEmployeeRepo->ListarEmployeesDeSubcontractor($subcontractor_id);
      foreach ($employees as $employee) {
         $arreglo_resultado[] = [
            'employee_id' => $employee->getEmployeeId(),
            'name' => $employee->getName(),
            'hourlyRate' => $employee->getHourlyRate(),
            'position' => $employee->getPosition(),
         ];
      }

      return $arreglo_resultado;
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

      $entity = $this->getDoctrine()->getRepository(SubcontractorEmployee::class)
         ->find($employee_id);
      /** @var SubcontractorEmployee $entity */
      if ($entity != null) {

         $arreglo_resultado['employee_id'] = $employee_id;
         $arreglo_resultado['name'] = $entity->getName();
         $arreglo_resultado['hourly_rate'] = $entity->getHourlyRate();
         $arreglo_resultado['position'] = $entity->getPosition();

         $resultado['success'] = true;
         $resultado['employee'] = $arreglo_resultado;
      }

      return $resultado;
   }

   /**
    * SalvarEmployee
    * @return array
    */
   public function SalvarEmployee($employee_id, $subcontractor_id, $name, $hourly_rate, $position)
   {

      $em = $this->getDoctrine()->getManager();

      $subcontractor_entity = $this->getDoctrine()->getRepository(Subcontractor::class)
         ->find($subcontractor_id);
      /** @var Subcontractor $subcontractor_entity */
      if ($subcontractor_entity != null) {

         $entity = null;
         $is_new = false;

         if (is_numeric($employee_id)) {
            $entity = $this->getDoctrine()->getRepository(SubcontractorEmployee::class)
               ->find($employee_id);
         }

         if ($entity == null) {
            $entity = new SubcontractorEmployee();
            $is_new = true;
         }

         $entity->setName($name);
         $entity->setHourlyRate($hourly_rate);
         $entity->setPosition($position);

         $entity->setSubcontractor($subcontractor_entity);

         $log_operacion = "Add";
         $log_descripcion = "The employee: $name is add to the subcontractor: " . $subcontractor_entity->getName();

         if ($is_new) {
            $em->persist($entity);
         } else {
            $log_operacion = "Update";
            $log_descripcion = "The employee: $name is modified to the subcontractor: " . $subcontractor_entity->getName();
         }

         $em->flush();

         //Salvar log
         $log_categoria = "Subcontractor Employee";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
         $resultado['employee_id'] = $entity->getEmployeeId();
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The subcontractor not exist.";
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

      $entity = $this->getDoctrine()->getRepository(SubcontractorEmployee::class)
         ->find($employee_id);
      /**@var SubcontractorEmployee $entity */
      if ($entity != null) {
         $name = $entity->getName();
         $subcontractor_name = $entity->getSubcontractor()->getName();

         // eliminar labor
         /** @var DataTrackingLaborRepository $dataTrackingLaborRepo */
         $dataTrackingLaborRepo = $this->getDoctrine()->getRepository(DataTrackingLabor::class);
         $labors = $dataTrackingLaborRepo->ListarDataTrackingsDeEmployeeSubcontractor($employee_id);
         foreach ($labors as $labor) {
            $em->remove($labor);
         }

         $em->remove($entity);

         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Subcontractor Employee";
         $log_descripcion = "The employee: $name is delete from subcontractor: $subcontractor_name";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

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
   public function ListarEmployees($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $subcontractor_id)
   {
      /** @var SubcontractorEmployeeRepository $subcontractorEmployeeRepo */
      $subcontractorEmployeeRepo = $this->getDoctrine()->getRepository(SubcontractorEmployee::class);
      $resultado = $subcontractorEmployeeRepo->ListarEmployeesConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $subcontractor_id);

      $data = [];

      foreach ($resultado['data'] as $value) {
         $employee_id = $value->getEmployeeId();

         $data[] = array(
            "id" => $employee_id,
            "name" => $value->getName(),
            "hourlyRate" => $value->getHourlyRate(),
            "position" => $value->getPosition(),
         );
      }

      return [
         'data' => $data,
         'total' => $resultado['total'], // ya viene con el filtro aplicado
      ];
   }

   /**
    * EliminarNotes: Elimina un notes en la BD
    * @param int $notes_id Id
    * @author Marcel
    */
   public function EliminarNotes($notes_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(SubcontractorNotes::class)
         ->find($notes_id);
      /**@var SubcontractorNotes $entity */
      if ($entity != null) {
         $notes = $entity->getNotes();
         $subcontractor_name = $entity->getSubcontractor()->getName();

         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Subcontractor Notes";
         $log_descripcion = "The notes: $notes is delete from subcontractor: $subcontractor_name";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * EliminarNotesDate: Elimina un notes en un rango de fechas en la BD
    * @param int $subcontractor_id Id
    * @author Marcel
    */
   public function EliminarNotesDate($subcontractor_id, $from, $to)
   {
      $em = $this->getDoctrine()->getManager();

      $subcontractor_entity = $this->getDoctrine()->getRepository(Subcontractor::class)
         ->find($subcontractor_id);
      /** @var Subcontractor $subcontractor_entity */
      if ($subcontractor_entity != null) {

         $subcontractor_name = $subcontractor_entity->getName();


         /** @var SubcontractorNotesRepository $subcontractorNotesRepo */
         $subcontractorNotesRepo = $this->getDoctrine()->getRepository(SubcontractorNotes::class);
         $notes = $subcontractorNotesRepo->ListarNotesDeSubcontractor($subcontractor_id, $from, $to);
         foreach ($notes as $entity) {
            $em->remove($entity);
         }

         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Subcontractor Notes";
         $log_descripcion = "The notes $from and $to is delete from subcontractor: $subcontractor_name";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * CargarDatosNotes: Carga los datos de un notes
    *
    * @param int $notes_id Id
    *
    * @author Marcel
    */
   public function CargarDatosNotes($notes_id)
   {
      $resultado = array();
      $arreglo_resultado = array();

      $entity = $this->getDoctrine()->getRepository(SubcontractorNotes::class)
         ->find($notes_id);
      /** @var SubcontractorNotes $entity */
      if ($entity != null) {

         $arreglo_resultado['notes_id'] = $notes_id;
         $arreglo_resultado['notes'] = $entity->getNotes();
         $arreglo_resultado['date'] = $entity->getDate()->format('m/d/Y');

         $resultado['success'] = true;
         $resultado['notes'] = $arreglo_resultado;
      }

      return $resultado;
   }

   /**
    * SalvarNotes
    * @param $notes_id
    * @param $subcontractor_id
    * @param $notes
    * @param $date
    * @return array
    */
   public function SalvarNotes($notes_id, $subcontractor_id, $notes, $date)
   {

      $em = $this->getDoctrine()->getManager();

      $subcontractor_entity = $this->getDoctrine()->getRepository(Subcontractor::class)
         ->find($subcontractor_id);
      /** @var Subcontractor $subcontractor_entity */
      if ($subcontractor_entity != null) {

         $entity = null;
         $is_new = false;

         if (is_numeric($notes_id)) {
            $entity = $this->getDoctrine()->getRepository(SubcontractorNotes::class)
               ->find($notes_id);
         }

         if ($entity == null) {
            $entity = new SubcontractorNotes();
            $is_new = true;
         }

         $entity->setNotes($notes);

         if ($date != '') {
            $date = \DateTime::createFromFormat('m/d/Y', $date);
            $entity->setDate($date);
         }

         $entity->setSubcontractor($subcontractor_entity);

         $log_operacion = "Add";
         $log_descripcion = "The notes: $notes is add to the subcontractor: " . $subcontractor_entity->getName();

         if ($is_new) {
            $em->persist($entity);
         } else {
            $log_operacion = "Update";
            $log_descripcion = "The notes: $notes is modified to the subcontractor: " . $subcontractor_entity->getName();
         }

         $em->flush();

         //Salvar log
         $log_categoria = "Subcontractor Notes";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The subcontractor not exist.";
      }

      return $resultado;
   }

   /**
    * ListarNotes: Listar los notes
    *
    * @param int $start Inicio
    * @param int $limit Limite
    * @param string $sSearch Para buscar
    *
    * @author Marcel
    */
   public function ListarNotes($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $subcontractor_id, $fecha_inicial, $fecha_fin)
   {
      /** @var SubcontractorNotesRepository $subcontractorNotesRepo */
      $subcontractorNotesRepo = $this->getDoctrine()->getRepository(SubcontractorNotes::class);
      $resultado = $subcontractorNotesRepo->ListarNotesConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $subcontractor_id, $fecha_inicial, $fecha_fin);

      $data = [];

      foreach ($resultado['data'] as $value) {
         $notes_id = $value->getId();

         $notes = $value->getNotes();
         $notes = mb_convert_encoding($notes, 'UTF-8', 'UTF-8');

         $data[] = array(
            "id" => $notes_id,
            "notes" => $notes,
            "date" => $value->getDate()->format('m/d/Y'),
         );
      }

      return [
         'data' => $data,
         'total' => $resultado['total'],
      ];
   }

   /**
    * CargarDatosSubcontractor: Carga los datos de un subcontractor
    *
    * @param int $subcontractor_id Id
    *
    * @author Marcel
    */
   public function CargarDatosSubcontractor($subcontractor_id)
   {
      $resultado = array();
      $arreglo_resultado = array();

      $entity = $this->getDoctrine()->getRepository(Subcontractor::class)
         ->find($subcontractor_id);
      /** @var Subcontractor $entity */
      if ($entity != null) {

         $arreglo_resultado['name'] = $entity->getName();
         $arreglo_resultado['phone'] = $entity->getPhone();
         $arreglo_resultado['address'] = $entity->getAddress();

         $arreglo_resultado['contactName'] = $entity->getContactName();
         $arreglo_resultado['contactEmail'] = $entity->getContactEmail();

         $arreglo_resultado['companyName'] = $entity->getCompanyName();
         $arreglo_resultado['companyPhone'] = $entity->getCompanyPhone();
         $arreglo_resultado['companyAddress'] = $entity->getCompanyAddress();

         // projects
         $projects = $this->ListarProjects($subcontractor_id);
         $arreglo_resultado['projects'] = $projects;

         $resultado['success'] = true;
         $resultado['subcontractor'] = $arreglo_resultado;
      }

      return $resultado;
   }

   /**
    * ListarProjects
    * @param $subcontractor_id
    * @return array
    */
   public function ListarProjects($subcontractor_id)
   {
      $projects = [];

      /** @var DataTrackingSubcontractRepository $dataTrackingSubcontractRepo */
      $dataTrackingSubcontractRepo = $this->getDoctrine()->getRepository(DataTrackingSubcontract::class);
      $subcontractor_projects = $dataTrackingSubcontractRepo->ListarProjectsDeSubcontractor($subcontractor_id);

      foreach ($subcontractor_projects as $key => $subcontractor_project) {
         $value = $subcontractor_project->getDataTracking()->getProject();
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
            "county" => $this->getCountiesDescriptionForProject($value),
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
    * EliminarSubcontractor: Elimina un rol en la BD
    * @param int $subcontractor_id Id
    * @author Marcel
    */
   public function EliminarSubcontractor($subcontractor_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Subcontractor::class)
         ->find($subcontractor_id);
      /**@var Subcontractor $entity */
      if ($entity != null) {

         // eliminar informacion
         $this->EliminarInformacionDeSubcontractor($subcontractor_id);

         $subcontractor_descripcion = $entity->getName();


         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Subcontractor";
         $log_descripcion = "The subcontractor is deleted: $subcontractor_descripcion";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * EliminarSubcontractors: Elimina los subcontractors seleccionados en la BD
    * @param int $ids Ids
    * @author Marcel
    */
   public function EliminarSubcontractors($ids)
   {
      $em = $this->getDoctrine()->getManager();

      if ($ids != "") {
         $ids = explode(',', $ids);
         $cant_eliminada = 0;
         $cant_total = 0;
         foreach ($ids as $subcontractor_id) {
            if ($subcontractor_id != "") {
               $cant_total++;
               $entity = $this->getDoctrine()->getRepository(Subcontractor::class)
                  ->find($subcontractor_id);
               /**@var Subcontractor $entity */
               if ($entity != null) {

                  // eliminar informacion
                  $this->EliminarInformacionDeSubcontractor($subcontractor_id);

                  $subcontractor_descripcion = $entity->getName();

                  $em->remove($entity);
                  $cant_eliminada++;

                  //Salvar log
                  $log_operacion = "Delete";
                  $log_categoria = "Subcontractor";
                  $log_descripcion = "The subcontractor is deleted: $subcontractor_descripcion";
                  $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
               }
            }
         }
      }
      $em->flush();

      if ($cant_eliminada == 0) {
         $resultado['success'] = false;
         $resultado['error'] = "The subcontractors could not be deleted, because they are associated with a subcontractor";
      } else {
         $resultado['success'] = true;

         $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected subcontractors because they are associated with a subcontractor";
         $resultado['message'] = $mensaje;
      }

      return $resultado;
   }

   /**
    * EliminarInformacionDeSubcontractor
    * @param $subcontractor_id
    * @return void
    */
   private function EliminarInformacionDeSubcontractor($subcontractor_id)
   {
      $em = $this->getDoctrine()->getManager();

      // employees
      /** @var SubcontractorEmployeeRepository $subcontractorEmployeeRepo */
      $subcontractorEmployeeRepo = $this->getDoctrine()->getRepository(SubcontractorEmployee::class);
      $employees = $subcontractorEmployeeRepo->ListarEmployeesDeSubcontractor($subcontractor_id);
      foreach ($employees as $employee) {

         // eliminar labor
         /** @var DataTrackingLaborRepository $dataTrackingLaborRepo */
         $dataTrackingLaborRepo = $this->getDoctrine()->getRepository(DataTrackingLabor::class);
         $labors = $dataTrackingLaborRepo->ListarDataTrackingsDeEmployeeSubcontractor($employee->getEmployeeId());
         foreach ($labors as $labor) {
            $em->remove($labor);
         }

         $em->remove($employee);
      }

      // notes
      /** @var SubcontractorNotesRepository $subcontractorNotesRepo */
      $subcontractorNotesRepo = $this->getDoctrine()->getRepository(SubcontractorNotes::class);
      $notes = $subcontractorNotesRepo->ListarNotesDeSubcontractor($subcontractor_id);
      foreach ($notes as $note) {
         $em->remove($note);
      }

      // datatracking
      /** @var DataTrackingSubcontractRepository $dataTrackingSubcontractRepo */
      $dataTrackingSubcontractRepo = $this->getDoctrine()->getRepository(DataTrackingSubcontract::class);
      $datatrackings = $dataTrackingSubcontractRepo->ListarSubcontractsDeSubcontractor($subcontractor_id);
      foreach ($datatrackings as $datatracking) {
         $em->remove($datatracking);
      }
   }

   /**
    * ActualizarSubcontractor: Actuializa los datos del rol en la BD
    * @param int $subcontractor_id Id
    * @author Marcel
    */
   public function ActualizarSubcontractor($subcontractor_id, $name, $phone, $address, $contactName, $contactEmail, $companyName, $companyPhone, $companyAddress)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Subcontractor::class)
         ->find($subcontractor_id);
      /** @var Subcontractor $entity */
      if ($entity != null) {
         //Verificar description
         $subcontractor = $this->getDoctrine()->getRepository(Subcontractor::class)
            ->findOneBy(['name' => $name]);
         if ($subcontractor != null && $entity->getSubcontractorId() != $subcontractor->getSubcontractorId()) {
            $resultado['success'] = false;
            $resultado['error'] = "The subcontractor name is in use, please try entering another one.";
            return $resultado;
         }

         $entity->setName($name);
         $entity->setPhone($phone);
         $entity->setAddress($address);
         $entity->setContactName($contactName);
         $entity->setContactEmail($contactEmail);

         $entity->setCompanyName($companyName);
         $entity->setCompanyPhone($companyPhone);
         $entity->setCompanyAddress($companyAddress);

         $entity->setUpdatedAt(new \DateTime());

         $em->flush();

         //Salvar log
         $log_operacion = "Update";
         $log_categoria = "Subcontractor";
         $log_descripcion = "The subcontractor is modified: $name";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;

         return $resultado;
      }
   }

   /**
    * SalvarSubcontractor: Guarda los datos de subcontractor en la BD
    * @param string $description Nombre
    * @author Marcel
    */
   public function SalvarSubcontractor($name, $phone, $address, $contactName, $contactEmail, $companyName, $companyPhone, $companyAddress)
   {
      $em = $this->getDoctrine()->getManager();

      //Verificar email
      $subcontractor = $this->getDoctrine()->getRepository(Subcontractor::class)
         ->findOneBy(['name' => $name]);
      if ($subcontractor != null) {
         $resultado['success'] = false;
         $resultado['error'] = "The subcontractor name is in use, please try entering another one.";
         return $resultado;
      }

      $entity = new Subcontractor();

      $entity->setName($name);
      $entity->setPhone($phone);
      $entity->setAddress($address);
      $entity->setContactName($contactName);
      $entity->setContactEmail($contactEmail);

      $entity->setCompanyName($companyName);
      $entity->setCompanyPhone($companyPhone);
      $entity->setCompanyAddress($companyAddress);

      $entity->setCreatedAt(new \DateTime());

      $em->persist($entity);

      $em->flush();

      //Salvar log
      $log_operacion = "Add";
      $log_categoria = "Subcontractor";
      $log_descripcion = "The subcontractor is added: $name";
      $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

      $resultado['success'] = true;

      return $resultado;
   }

   /**
    * ListarSubcontractors: Listar los subcontractors
    *
    * @param int $start Inicio
    * @param int $limit Limite
    * @param string $sSearch Para buscar
    *
    * @author Marcel
    */
   public function ListarSubcontractors($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
   {
      /** @var SubcontractorRepository $subcontractorRepo */
      $subcontractorRepo = $this->getDoctrine()->getRepository(Subcontractor::class);
      $resultado = $subcontractorRepo->ListarSubcontractorsConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

      $data = [];

      foreach ($resultado['data'] as $value) {
         $subcontractor_id = $value->getSubcontractorId();

         $data[] = array(
            "id" => $subcontractor_id,
            "name" => $value->getName(),
            "phone" => $value->getPhone() ?? '',
            "address" => $value->getAddress(),
            "contactName" => $value->getContactName(),
            "contactEmail" => $value->getContactEmail(),
            "companyName" => $value->getCompanyName(),
            "companyPhone" => $value->getCompanyPhone() ?? '',
            "companyAddress" => $value->getCompanyAddress(),
         );
      }

      return [
         'data' => $data,
         'total' => $resultado['total'], // ya viene con el filtro aplicado
      ];
   }
}
