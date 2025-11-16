<?php

namespace App\Utils\Admin;

use App\Entity\Race;
use App\Repository\RaceRepository;
use App\Repository\EmployeeRepository;
use App\Entity\Employee;
use App\Utils\Base;

class RaceService extends Base
{

   /**
    * ListarOrdenados
    * @return array
    */
   public function ListarOrdenados()
   {
      $races = [];

      /** @var RaceRepository $raceRepo */
      $raceRepo = $this->getDoctrine()->getRepository(Race::class);
      $lista = $raceRepo->ListarOrdenados();

      foreach ($lista as $value) {
         $races[] = [
            'race_id' => $value->getRaceId(),
            'code' => $value->getCode(),
            'description' => $value->getDescription(),
            'classification' => $value->getClassification(),
         ];
      }

      return $races;
   }

   /**
    * CargarDatosRace: Carga los datos de un race
    *
    * @param int $race_id Id
    *
    * @author Marcel
    */
   public function CargarDatosRace($race_id)
   {
      $resultado = array();
      $arreglo_resultado = array();

      $entity = $this->getDoctrine()->getRepository(Race::class)
         ->find($race_id);
      /** @var Race $entity */
      if ($entity != null) {

         $arreglo_resultado['code'] = $entity->getCode();
         $arreglo_resultado['description'] = $entity->getDescription();
         $arreglo_resultado['classification'] = $entity->getClassification();

         $resultado['success'] = true;
         $resultado['race'] = $arreglo_resultado;
      }

      return $resultado;
   }

   /**
    * EliminarRace: Elimina un race en la BD
    * @param int $race_id Id
    * @author Marcel
    */
   public function EliminarRace($race_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Race::class)
         ->find($race_id);
      /**@var Race $entity */
      if ($entity != null) {

         $race_descripcion = $entity->getDescription();

         // employees
         /** @var EmployeeRepository $employeeRepo */
         $employeeRepo = $this->getDoctrine()->getRepository(Employee::class);
         $employees = $employeeRepo->ListarEmployeesDeRace($race_id);
         if (count($employees) > 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The race could not be deleted, because it is related to a employee";
            return $resultado;
         }


         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Race";
         $log_descripcion = "The race is deleted: $race_descripcion";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * EliminarRaces: Elimina los races seleccionados en la BD
    * @param int $ids Ids
    * @author Marcel
    */
   public function EliminarRaces($ids)
   {
      $em = $this->getDoctrine()->getManager();

      if ($ids != "") {
         $ids = explode(',', $ids);
         $cant_eliminada = 0;
         $cant_total = 0;
         foreach ($ids as $race_id) {
            if ($race_id != "") {
               $cant_total++;
               $entity = $this->getDoctrine()->getRepository(Race::class)
                  ->find($race_id);
               /**@var Race $entity */
               if ($entity != null) {

                  // employees
                  /** @var EmployeeRepository $employeeRepo */
                  $employeeRepo = $this->getDoctrine()->getRepository(Employee::class);
                  $employees = $employeeRepo->ListarEmployeesDeRace($race_id);
                  if (count($employees) === 0) {

                     $race_descripcion = $entity->getDescription();

                     $em->remove($entity);
                     $cant_eliminada++;

                     //Salvar log
                     $log_operacion = "Delete";
                     $log_categoria = "Race";
                     $log_descripcion = "The race is deleted: $race_descripcion";
                     $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                  }
               }
            }
         }
      }
      $em->flush();

      if ($cant_eliminada == 0) {
         $resultado['success'] = false;
         $resultado['error'] = "The races could not be deleted, because they are associated with a employee";
      } else {
         $resultado['success'] = true;

         $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected races because they are associated with a employee";
         $resultado['message'] = $mensaje;
      }

      return $resultado;
   }

   /**
    * ActualizarRace: Actuializa los datos del rol en la BD
    * @param int $race_id Id
    * @author Marcel
    */
   public function ActualizarRace($race_id, $code, $description, $classification)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Race::class)
         ->find($race_id);
      /** @var Race $entity */
      if ($entity != null) {
         //Verificar code
         $race = $this->getDoctrine()->getRepository(Race::class)
            ->findOneBy(['code' => $code]);
         if ($race != null && $entity->getRaceId() != $race->getRaceId()) {
            $resultado['success'] = false;
            $resultado['error'] = "The race code is in use, please try entering another one.";
            return $resultado;
         }

         $entity->setCode($code);
         $entity->setDescription($description);
         $entity->setClassification($classification);

         $em->flush();

         //Salvar log
         $log_operacion = "Update";
         $log_categoria = "Race";
         $log_descripcion = "The race is modified: $description";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
         $resultado['race_id'] = $entity->getRaceId();

         return $resultado;
      }
   }

   /**
    * SalvarRace: Guarda los datos de race en la BD
    * @param string $description Nombre
    * @author Marcel
    */
   public function SalvarRace($code, $description, $classification)
   {
      $em = $this->getDoctrine()->getManager();

      //Verificar name
      $race = $this->getDoctrine()->getRepository(Race::class)
         ->findOneBy(['code' => $code]);
      if ($race != null) {
         $resultado['success'] = false;
         $resultado['error'] = "The race code is in use, please try entering another one.";
         return $resultado;
      }

      $entity = new Race();

      $entity->setCode($code);
      $entity->setDescription($description);
      $entity->setClassification($classification);

      $em->persist($entity);

      $em->flush();

      //Salvar log
      $log_operacion = "Add";
      $log_categoria = "Race";
      $log_descripcion = "The race is added: $description";
      $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

      $resultado['success'] = true;
      $resultado['race_id'] = $entity->getRaceId();

      return $resultado;
   }

   /**
    * ListarRaces: Listar los races
    *
    * @param int $start Inicio
    * @param int $limit Limite
    * @param string $sSearch Para buscar
    *
    * @author Marcel
    */
   public function ListarRaces($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
   {
      /** @var RaceRepository $raceRepo */
      $raceRepo = $this->getDoctrine()->getRepository(Race::class);
      $resultado = $raceRepo->ListarRacesConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

      $data = [];

      foreach ($resultado['data'] as $value) {
         $race_id = $value->getRaceId();

         $data[] = array(
            "id" => $race_id,
            "code" => $value->getCode(),
            "description" => $value->getDescription(),
            "classification" => $value->getClassification(),
         );
      }

      return [
         'data' => $data,
         'total' => $resultado['total'], // ya viene con el filtro aplicado
      ];
   }
}
