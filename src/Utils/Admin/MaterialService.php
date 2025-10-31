<?php

namespace App\Utils\Admin;

use App\Entity\DataTrackingMaterial;
use App\Entity\Material;
use App\Entity\Unit;
use App\Repository\DataTrackingMaterialRepository;
use App\Repository\MaterialRepository;
use App\Utils\Base;

class MaterialService extends Base
{

   /**
    * CargarDatosMaterial: Carga los datos de un material
    *
    * @param int $material_id Id
    *
    * @author Marcel
    */
   public function CargarDatosMaterial($material_id)
   {
      $resultado = array();
      $arreglo_resultado = array();

      $entity = $this->getDoctrine()->getRepository(Material::class)
         ->find($material_id);
      /** @var Material $entity */
      if ($entity != null) {

         $arreglo_resultado['name'] = $entity->getName();
         $arreglo_resultado['price'] = $entity->getPrice();
         $arreglo_resultado['unit_id'] = $entity->getUnit()->getUnitId();

         $resultado['success'] = true;
         $resultado['material'] = $arreglo_resultado;
      }

      return $resultado;
   }

   /**
    * EliminarMaterial: Elimina un rol en la BD
    * @param int $material_id Id
    * @author Marcel
    */
   public function EliminarMaterial($material_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Material::class)
         ->find($material_id);
      /**@var Material $entity */
      if ($entity != null) {

         // materials
         /** @var DataTrackingMaterialRepository $dataTrackingMaterialRepo */
         $dataTrackingMaterialRepo = $this->getDoctrine()->getRepository(DataTrackingMaterial::class);
         $data_tracking_materials = $dataTrackingMaterialRepo->ListarDataTrackingsDeMaterial($material_id);
         foreach ($data_tracking_materials as $data_tracking_material) {
            $em->remove($data_tracking_material);
         }

         $material_descripcion = $entity->getName();

         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Material";
         $log_descripcion = "The material is deleted: $material_descripcion";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * EliminarMaterials: Elimina los materials seleccionados en la BD
    * @param int $ids Ids
    * @author Marcel
    */
   public function EliminarMaterials($ids)
   {
      $em = $this->getDoctrine()->getManager();

      if ($ids != "") {
         $ids = explode(',', $ids);
         $cant_eliminada = 0;
         $cant_total = 0;
         foreach ($ids as $material_id) {
            if ($material_id != "") {
               $cant_total++;
               $entity = $this->getDoctrine()->getRepository(Material::class)
                  ->find($material_id);
               /**@var Material $entity */
               if ($entity != null) {

                  // materials
                  /** @var DataTrackingMaterialRepository $dataTrackingMaterialRepo */
                  $dataTrackingMaterialRepo = $this->getDoctrine()->getRepository(DataTrackingMaterial::class);
                  $data_tracking_materials = $dataTrackingMaterialRepo->ListarDataTrackingsDeMaterial($material_id);
                  foreach ($data_tracking_materials as $data_tracking_material) {
                     $em->remove($data_tracking_material);
                  }

                  $material_descripcion = $entity->getName();

                  $em->remove($entity);
                  $cant_eliminada++;

                  //Salvar log
                  $log_operacion = "Delete";
                  $log_categoria = "Material";
                  $log_descripcion = "The material is deleted: $material_descripcion";
                  $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
               }
            }
         }
      }
      $em->flush();

      if ($cant_eliminada == 0) {
         $resultado['success'] = false;
         $resultado['error'] = "The materials could not be deleted, because they are associated with a projects or invoices";
      } else {
         $resultado['success'] = true;

         $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected materials because they are associated with a projects or invoices";
         $resultado['message'] = $mensaje;
      }

      return $resultado;
   }

   /**
    * ActualizarMaterial: Actuializa los datos del rol en la BD
    * @param int $material_id Id
    * @author Marcel
    */
   public function ActualizarMaterial($material_id, $unit_id, $name, $price)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Material::class)
         ->find($material_id);
      /** @var Material $entity */
      if ($entity != null) {
         //Verificar name
         $material = $this->getDoctrine()->getRepository(Material::class)
            ->findOneBy(['name' => $name]);
         if ($material != null && $entity->getMaterialId() != $material->getMaterialId()) {
            $resultado['success'] = false;
            $resultado['error'] = "The material name is in use, please try entering another one.";
            return $resultado;
         }

         $entity->setName($name);
         $entity->setPrice($price);

         if ($unit_id != '') {
            $unit = $this->getDoctrine()->getRepository(Unit::class)->find($unit_id);
            $entity->setUnit($unit);
         }

         $em->flush();

         //Salvar log
         $log_operacion = "Update";
         $log_categoria = "Material";
         $log_descripcion = "The material is modified: $name";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;

         return $resultado;
      }
   }

   /**
    * SalvarMaterial: Guarda los datos de material en la BD
    * @param string $name Nombre
    * @author Marcel
    */
   public function SalvarMaterial($unit_id, $name, $price)
   {
      $em = $this->getDoctrine()->getManager();

      //Verificar name
      $material = $this->getDoctrine()->getRepository(Material::class)
         ->findOneBy(['name' => $name]);
      if ($material != null) {
         $resultado['success'] = false;
         $resultado['error'] = "The material name is in use, please try entering another one.";
         return $resultado;
      }

      $entity = new Material();

      $entity->setName($name);
      $entity->setPrice($price);

      if ($unit_id != '') {
         $unit = $this->getDoctrine()->getRepository(Unit::class)->find($unit_id);
         $entity->setUnit($unit);
      }

      $em->persist($entity);

      $em->flush();

      //Salvar log
      $log_operacion = "Add";
      $log_categoria = "Material";
      $log_descripcion = "The material is added: $name";
      $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

      $resultado['success'] = true;

      return $resultado;
   }

   /**
    * ListarMaterials: Listar los materials
    *
    * @param int $start Inicio
    * @param int $limit Limite
    * @param string $sSearch Para buscar
    *
    * @author Marcel
    */
   public function ListarMaterials($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
   {
      /** @var MaterialRepository $materialRepo */
      $materialRepo = $this->getDoctrine()->getRepository(Material::class);
      $resultado = $materialRepo->ListarMaterialsConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

      $data = [];

      foreach ($resultado['data'] as $value) {
         $material_id = $value->getMaterialId();

         $data[] = array(
            "id" => $material_id,
            "name" => $value->getName(),
            "price" => $value->getPrice(),
            "unit" => $value->getUnit() != null ? $value->getUnit()->getDescription() : '',
         );
      }

      return [
         'data' => $data,
         'total' => $resultado['total'], // ya viene con el filtro aplicado
      ];
   }
}
