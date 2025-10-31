<?php

namespace App\Utils\Admin;

use App\Entity\EstimateProjectType;
use App\Entity\ProjectType;
use App\Repository\EstimateProjectTypeRepository;
use App\Repository\ProjectTypeRepository;
use App\Utils\Base;

class ProjectTypeService extends Base
{
   /**
    * CargarDatosType: Carga los datos de un type
    *
    * @param int $type_id Id
    *
    * @author Marcel
    */
   public function CargarDatosType($type_id)
   {
      $resultado = array();
      $arreglo_resultado = array();

      $entity = $this->getDoctrine()->getRepository(ProjectType::class)
         ->find($type_id);
      /** @var ProjectType $entity */
      if ($entity != null) {

         $arreglo_resultado['description'] = $entity->getDescription();
         $arreglo_resultado['status'] = $entity->getStatus();

         $resultado['success'] = true;
         $resultado['type'] = $arreglo_resultado;
      }

      return $resultado;
   }

   /**
    * EliminarType: Elimina un type en la BD
    * @param int $type_id Id
    * @author Marcel
    */
   public function EliminarType($type_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(ProjectType::class)
         ->find($type_id);
      /**@var ProjectType $entity */
      if ($entity != null) {

         // eliminar info
         $this->EliminarInformacionDeType($type_id);

         $type_descripcion = $entity->getDescription();


         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Project Type";
         $log_descripcion = "The project type is deleted: $type_descripcion";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * EliminarTypes: Elimina los types seleccionados en la BD
    * @param int $ids Ids
    * @author Marcel
    */
   public function EliminarTypes($ids)
   {
      $em = $this->getDoctrine()->getManager();

      if ($ids != "") {
         $ids = explode(',', $ids);
         $cant_eliminada = 0;
         $cant_total = 0;
         foreach ($ids as $type_id) {
            if ($type_id != "") {
               $cant_total++;
               $entity = $this->getDoctrine()->getRepository(ProjectType::class)
                  ->find($type_id);
               /** @var ProjectType $entity */
               if ($entity != null) {

                  // eliminar info
                  $this->EliminarInformacionDeType($type_id);

                  $type_descripcion = $entity->getDescription();

                  $em->remove($entity);
                  $cant_eliminada++;

                  //Salvar log
                  $log_operacion = "Delete";
                  $log_categoria = "Project Type";
                  $log_descripcion = "The project type is deleted: $type_descripcion";
                  $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
               }
            }
         }
      }
      $em->flush();

      if ($cant_eliminada == 0) {
         $resultado['success'] = false;
         $resultado['error'] = "The project types could not be deleted, because they are associated with a invoice";
      } else {
         $resultado['success'] = true;

         $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected types because they are associated with a invoice";
         $resultado['message'] = $mensaje;
      }

      return $resultado;
   }

   /**
    * EliminarInformacionDeType
    * @param $type_id
    * @return void
    */
   public function EliminarInformacionDeType($type_id)
   {
      $em = $this->getDoctrine()->getManager();

      // estimates
      /** @var EstimateProjectTypeRepository $estimateProjectTypeRepo */
      $estimateProjectTypeRepo = $this->getDoctrine()->getRepository(EstimateProjectType::class);
      $estimates = $estimateProjectTypeRepo->ListarEstimatesDeType($type_id);
      foreach ($estimates as $estimate) {
         $em->remove($estimate);
      }
   }

   /**
    * ActualizarType: Actuializa los datos del type en la BD
    * @param int $type_id Id
    * @author Marcel
    */
   public function ActualizarType($type_id, $description, $status)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(ProjectType::class)
         ->find($type_id);
      /** @var ProjectType $entity */
      if ($entity != null) {

         //Verificar name
         $type = $this->getDoctrine()->getRepository(ProjectType::class)
            ->findOneBy(['description' => $description]);
         if ($type != null  && $entity->getTypeId() != $type->getTypeId()) {
            $resultado['success'] = false;
            $resultado['error'] = "The project type name is in use, please try entering another one.";
            return $resultado;
         }

         $entity->setDescription($description);
         $entity->setStatus($status);

         $em->flush();

         //Salvar log
         $log_operacion = "Update";
         $log_categoria = "Project Type";
         $log_descripcion = "The project type is modified: $description";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
         $resultado['type_id'] = $entity->getTypeId();

         return $resultado;
      }
   }

   /**
    * SalvarType: Guarda los datos de type en la BD
    * @param string $description Nombre
    * @author Marcel
    */
   public function SalvarType($description, $status)
   {
      $em = $this->getDoctrine()->getManager();

      //Verificar name
      $type = $this->getDoctrine()->getRepository(ProjectType::class)
         ->findOneBy(['description' => $description]);
      if ($type != null) {
         $resultado['success'] = false;
         $resultado['error'] = "The project type name is in use, please try entering another one.";
         return $resultado;
      }

      $entity = new ProjectType();

      $entity->setDescription($description);
      $entity->setStatus($status);

      $em->persist($entity);

      $em->flush();

      //Salvar log
      $log_operacion = "Add";
      $log_categoria = "Project Type";
      $log_descripcion = "The project type is added: $description";
      $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

      $resultado['success'] = true;
      $resultado['type_id'] = $entity->getTypeId();

      return $resultado;
   }

   /**
    * ListarTypes: Listar los types
    *
    * @param int $start Inicio
    * @param int $limit Limite
    * @param string $sSearch Para buscar
    *
    * @author Marcel
    */
   public function ListarTypes($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
   {
      /** @var ProjectTypeRepository $projectTypeRepo */
      $projectTypeRepo = $this->getDoctrine()->getRepository(ProjectType::class);
      $resultado = $projectTypeRepo->ListarTypesConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

      $data = [];

      foreach ($resultado['data'] as $value) {
         $type_id = $value->getTypeId();

         $data[] = array(
            "id" => $type_id,
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
