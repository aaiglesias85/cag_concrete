<?php

namespace App\Utils\Admin;

use App\Entity\DataTrackingLabor;
use App\Entity\Employee;
use App\Entity\Schedule;
use App\Entity\ScheduleEmployee;
use App\Repository\DataTrackingLaborRepository;
use App\Repository\EmployeeRepository;
use App\Repository\ScheduleEmployeeRepository;
use App\Repository\RaceRepository;
use App\Entity\Race;

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
    * ListarProjects
    * @param $employee_id
    * @return array
    */
   public function ListarProjects($employee_id)
   {
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

         $arreglo_resultado['address'] = $entity->getAddress();
         $arreglo_resultado['phone'] = $entity->getPhone();
         $arreglo_resultado['cert_rate_type'] = $entity->getCertRateType();
         $arreglo_resultado['social_security_number'] = $entity->getSocialSecurityNumber();
         $arreglo_resultado['apprentice_percentage'] = $entity->getApprenticePercentage();
         $arreglo_resultado['work_code'] = $entity->getWorkCode();
         $arreglo_resultado['gender'] = $entity->getGender();
         $arreglo_resultado['race'] = $entity->getRace() ? $entity->getRace()->getDescription() : "";
         $arreglo_resultado['date_hired'] = $entity->getDateHired() ? $entity->getDateHired()->format('m/d/Y') : "";
         $arreglo_resultado['date_terminated'] = $entity->getDateTerminated() ? $entity->getDateTerminated()->format('m/d/Y') : "";
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
    * ActualizarEmployee: Actuializa los datos del rol en la BD
    * @param int $employee_id Id
    * @author Marcel
    */
   public function ActualizarEmployee(
      $employee_id,
      $name,
      $hourly_rate,
      $position,
      $color,
      $address,
      $phone,
      $cert_rate_type,
      $social_security_number,
      $apprentice_percentage,
      $work_code,
      $gender,
      $race_id,
      $date_hired,
      $date_terminated,
      $reason_terminated,
      $time_card_notes,
      $regular_rate_per_hour,
      $overtime_rate_per_hour,
      $special_rate_per_hour,
      $trade_licenses_info,
      $notes,
      $is_osha_10_certified,
      $is_veteran,
      $status
   ) {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Employee::class)
         ->find($employee_id);
      /** @var Employee $entity */
      if ($entity != null) {

         //Verificar social security number
         if ($social_security_number != "") {
            $employee = $this->getDoctrine()->getRepository(Employee::class)
               ->findOneBy(['socialSecurityNumber' => $social_security_number]);
            if ($employee != null && $entity->getEmployeeId() != $employee->getEmployeeId()) {
               $resultado['success'] = false;
               $resultado['error'] = "The social security number is in use, please try entering another one.";
               return $resultado;
            }
         }

         $entity->setName($name);
         $entity->setHourlyRate($hourly_rate);
         $entity->setPosition($position);
         $entity->setColor($color);

         $entity->setAddress($address);
         $entity->setPhone($phone);
         $entity->setCertRateType($cert_rate_type);
         $entity->setSocialSecurityNumber($social_security_number);
         $entity->setApprenticePercentage($apprentice_percentage);
         $entity->setWorkCode($work_code);
         $entity->setGender($gender);

         if ($race_id != "") {
            $race = $this->getDoctrine()->getRepository(Race::class)
               ->find($race_id);
            $entity->setRace($race);
         }

         if ($date_hired != "") {
            $date_hired = \DateTime::createFromFormat('m/d/Y', $date_hired);
            $entity->setDateHired($date_hired);
         }

         if ($date_terminated != "") {
            $date_terminated = \DateTime::createFromFormat('m/d/Y', $date_terminated);
            $entity->setDateTerminated($date_terminated);
         }

         $entity->setReasonTerminated($reason_terminated);
         $entity->setTimeCardNotes($time_card_notes);
         $entity->setRegularRatePerHour($regular_rate_per_hour);
         $entity->setOvertimeRatePerHour($overtime_rate_per_hour);
         $entity->setSpecialRatePerHour($special_rate_per_hour);
         $entity->setTradeLicensesInfo($trade_licenses_info);
         $entity->setNotes($notes);
         $entity->setIsOsha10Certified($is_osha_10_certified);
         $entity->setIsVeteran($is_veteran);
         $entity->setStatus($status);

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
   public function SalvarEmployee(
      $name,
      $hourly_rate,
      $position,
      $color,
      $address,
      $phone,
      $cert_rate_type,
      $social_security_number,
      $apprentice_percentage,
      $work_code,
      $gender,
      $race_id,
      $date_hired,
      $date_terminated,
      $reason_terminated,
      $time_card_notes,
      $regular_rate_per_hour,
      $overtime_rate_per_hour,
      $special_rate_per_hour,
      $trade_licenses_info,
      $notes,
      $is_osha_10_certified,
      $is_veteran,
      $status
   ) {
      $em = $this->getDoctrine()->getManager();

      //Verificar social security number
      if ($social_security_number != "") {
         $employee = $this->getDoctrine()->getRepository(Employee::class)
            ->findOneBy(['socialSecurityNumber' => $social_security_number]);
         if ($employee != null) {
            $resultado['success'] = false;
            $resultado['error'] = "The social security number is in use, please try entering another one.";
            return $resultado;
         }
      }

      $entity = new Employee();

      $entity->setName($name);
      $entity->setHourlyRate($hourly_rate);
      $entity->setPosition($position);
      $entity->setColor($color);

      $entity->setAddress($address);
      $entity->setPhone($phone);
      $entity->setCertRateType($cert_rate_type);
      $entity->setSocialSecurityNumber($social_security_number);
      $entity->setApprenticePercentage($apprentice_percentage);
      $entity->setWorkCode($work_code);
      $entity->setGender($gender);

      if ($race_id != "") {
         $race = $this->getDoctrine()->getRepository(Race::class)
            ->find($race_id);
         $entity->setRace($race);
      }

      if ($date_hired != "") {
         $date_hired = \DateTime::createFromFormat('m/d/Y', $date_hired);
         $entity->setDateHired($date_hired);
      }

      if ($date_terminated != "") {
         $date_terminated = \DateTime::createFromFormat('m/d/Y', $date_terminated);
         $entity->setDateTerminated($date_terminated);
      }

      $entity->setReasonTerminated($reason_terminated);
      $entity->setTimeCardNotes($time_card_notes);
      $entity->setRegularRatePerHour($regular_rate_per_hour);
      $entity->setOvertimeRatePerHour($overtime_rate_per_hour);
      $entity->setSpecialRatePerHour($special_rate_per_hour);
      $entity->setTradeLicensesInfo($trade_licenses_info);
      $entity->setNotes($notes);
      $entity->setIsOsha10Certified($is_osha_10_certified);
      $entity->setIsVeteran($is_veteran);
      $entity->setStatus($status);

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
      /** @var EmployeeRepository $employeeRepo */
      $employeeRepo = $this->getDoctrine()->getRepository(Employee::class);
      $resultado = $employeeRepo->ListarEmployeesConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

      $data = [];

      foreach ($resultado['data'] as $value) {
         $employee_id = $value->getEmployeeId();

         $data[] = array(
            "id" => $employee_id,
            "name" => $value->getName(),
            "hourlyRate" => $value->getHourlyRate(),
            "position" => $value->getPosition(),
            "color" => $value->getColor(),
            "address" => $value->getAddress(),
            "phone" => $value->getPhone() ?? '',
            "certRateType" => $value->getCertRateType(),
            "socialSecurityNumber" => $value->getSocialSecurityNumber(),
            "apprenticePercentage" => $value->getApprenticePercentage(),
            "workCode" => $value->getWorkCode(),
            "race" => $value->getRace() ? $value->getRace()->getDescription() : "",
            "gender" => $value->getGender(),
            "dateHired" => $value->getDateHired() ? $value->getDateHired()->format('m/d/Y') : "",
            "dateTerminated" => $value->getDateTerminated() ? $value->getDateTerminated()->format('m/d/Y') : "",
            "reasonTerminated" => $value->getReasonTerminated(),
            "timeCardNotes" => $value->getTimeCardNotes(),
            "regularRatePerHour" => $value->getRegularRatePerHour(),
            "overtimeRatePerHour" => $value->getOvertimeRatePerHour(),
            "specialRatePerHour" => $value->getSpecialRatePerHour(),
            "tradeLicensesInfo" => $value->getTradeLicensesInfo(),
            "notes" => $value->getNotes(),
            "isOsha10Certified" => $value->getIsOsha10Certified(),
            "isVeteran" => $value->getIsVeteran(),
            "status" => $value->getStatus() ? 1 : 0,
         );
      }

      return [
         'data' => $data,
         'total' => $resultado['total'], // ya viene con el filtro aplicado
      ];
   }
}
