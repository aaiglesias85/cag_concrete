<?php

namespace App\Utils\Admin;

use App\Entity\Gender;
use App\Repository\GenderRepository;
use App\Utils\Base;

class GenderService extends Base
{

   /**
    * ListarOrdenados
    * @return array
    */
   public function ListarOrdenados()
   {
      $genders = [];

      /** @var GenderRepository $genderRepo */
      $genderRepo = $this->getDoctrine()->getRepository(Gender::class);
      $lista = $genderRepo->ListarOrdenados();

      foreach ($lista as $value) {
         $genders[] = [
            'gender_id' => $value->getGenderId(),
            'code' => $value->getCode(),
            'description' => $value->getDescription(),
            'classification' => $value->getClassification(),
         ];
      }

      return $genders;
   }

   /**
    * CargarDatosGender: Carga los datos de un gender
    *
    * @param int $gender_id Id
    *
    * @author Marcel
    */
   public function CargarDatosGender($gender_id)
   {
      $resultado = array();
      $arreglo_resultado = array();

      $entity = $this->getDoctrine()->getRepository(Gender::class)
         ->find($gender_id);
      /** @var Gender $entity */
      if ($entity != null) {

         $arreglo_resultado['code'] = $entity->getCode();
         $arreglo_resultado['description'] = $entity->getDescription();
         $arreglo_resultado['classification'] = $entity->getClassification();

         $resultado['success'] = true;
         $resultado['gender'] = $arreglo_resultado;
      }

      return $resultado;
   }

   /**
    * EliminarGender: Elimina un gender en la BD
    * @param int $gender_id Id
    * @author Marcel
    */
   public function EliminarGender($gender_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Gender::class)
         ->find($gender_id);
      /**@var Gender $entity */
      if ($entity != null) {

         $gender_descripcion = $entity->getDescription();


         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Gender";
         $log_descripcion = "The gender is deleted: $gender_descripcion";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * EliminarGenders: Elimina los genders seleccionados en la BD
    * @param int $ids Ids
    * @author Marcel
    */
   public function EliminarGenders($ids)
   {
      $em = $this->getDoctrine()->getManager();

      if ($ids != "") {
         $ids = explode(',', $ids);
         $cant_eliminada = 0;
         $cant_total = 0;
         foreach ($ids as $gender_id) {
            if ($gender_id != "") {
               $cant_total++;
               $entity = $this->getDoctrine()->getRepository(Gender::class)
                  ->find($gender_id);
               /**@var Gender $entity */
               if ($entity != null) {

                  $gender_descripcion = $entity->getDescription();

                  $em->remove($entity);
                  $cant_eliminada++;

                  //Salvar log
                  $log_operacion = "Delete";
                  $log_categoria = "Gender";
                  $log_descripcion = "The gender is deleted: $gender_descripcion";
                  $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
               }
            }
         }
      }
      $em->flush();

      if ($cant_eliminada == 0) {
         $resultado['success'] = false;
         $resultado['error'] = "The genders could not be deleted, because they are associated with a project";
      } else {
         $resultado['success'] = true;

         $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected genders because they are associated with a project";
         $resultado['message'] = $mensaje;
      }

      return $resultado;
   }

   /**
    * ActualizarGender: Actuializa los datos del rol en la BD
    * @param int $gender_id Id
    * @author Marcel
    */
   public function ActualizarGender($gender_id, $code, $description, $classification)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Gender::class)
         ->find($gender_id);
      /** @var Gender $entity */
      if ($entity != null) {
         //Verificar code
         $gender = $this->getDoctrine()->getRepository(Gender::class)
            ->findOneBy(['code' => $code]);
         if ($gender != null && $entity->getGenderId() != $gender->getGenderId()) {
            $resultado['success'] = false;
            $resultado['error'] = "The gender code is in use, please try entering another one.";
            return $resultado;
         }

         $entity->setCode($code);
         $entity->setDescription($description);
         $entity->setClassification($classification);

         $em->flush();

         //Salvar log
         $log_operacion = "Update";
         $log_categoria = "Gender";
         $log_descripcion = "The gender is modified: $description";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
         $resultado['gender_id'] = $entity->getGenderId();

         return $resultado;
      }
   }

   /**
    * SalvarGender: Guarda los datos de gender en la BD
    * @param string $description Nombre
    * @author Marcel
    */
   public function SalvarGender($code, $description, $classification)
   {
      $em = $this->getDoctrine()->getManager();

      //Verificar name
      $gender = $this->getDoctrine()->getRepository(Gender::class)
         ->findOneBy(['code' => $code]);
      if ($gender != null) {
         $resultado['success'] = false;
         $resultado['error'] = "The gender code is in use, please try entering another one.";
         return $resultado;
      }

      $entity = new Gender();

      $entity->setCode($code);
      $entity->setDescription($description);
      $entity->setClassification($classification);

      $em->persist($entity);

      $em->flush();

      //Salvar log
      $log_operacion = "Add";
      $log_categoria = "Gender";
      $log_descripcion = "The gender is added: $description";
      $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

      $resultado['success'] = true;
      $resultado['gender_id'] = $entity->getGenderId();

      return $resultado;
   }

   /**
    * ListarGenders: Listar los genders
    *
    * @param int $start Inicio
    * @param int $limit Limite
    * @param string $sSearch Para buscar
    *
    * @author Marcel
    */
   public function ListarGenders($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
   {
      /** @var GenderRepository $genderRepo */
      $genderRepo = $this->getDoctrine()->getRepository(Gender::class);
      $resultado = $genderRepo->ListarGendersConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

      $data = [];

      foreach ($resultado['data'] as $value) {
         $gender_id = $value->getGenderId();

         $data[] = array(
            "id" => $gender_id,
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
