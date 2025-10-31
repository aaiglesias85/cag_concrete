<?php

namespace App\Utils\Admin;

use App\Entity\Advertisement;
use App\Repository\AdvertisementRepository;
use App\Utils\Base;

class AdvertisementService extends Base
{

   /**
    * ListarAdvertisementsUltimosDias: Lista los advertisements para el header
    * @author Marcel
    */
   public function ListarAdvertisementsUltimosDias()
   {
      $arreglo_resultado = array();
      $cont = 0;

      $fecha_actual = $this->ObtenerFechaActual('m/d/Y');

      /** @var AdvertisementRepository $advertisementRepo */
      $advertisementRepo = $this->getDoctrine()->getRepository(Advertisement::class);
      $lista = $advertisementRepo->ListarOrdenados($fecha_actual, $fecha_actual);

      foreach ($lista as $value) {

         $arreglo_resultado[$cont]['advertisement_id'] = $value->getAdvertisementId();
         $arreglo_resultado[$cont]['title'] = $value->getTitle();

         $description = $value->getDescription();
         $arreglo_resultado[$cont]['description'] = $this->truncate(strip_tags($description), 150);
         $arreglo_resultado[$cont]['description_html'] = $description;


         $cont++;
      }

      return $arreglo_resultado;
   }

   /**
    * CargarDatosAdvertisement: Carga los datos de un advertisement
    *
    * @param int $advertisement_id Id
    *
    * @author Marcel
    */
   public function CargarDatosAdvertisement($advertisement_id)
   {
      $resultado = array();
      $arreglo_resultado = array();

      $entity = $this->getDoctrine()->getRepository(Advertisement::class)
         ->find($advertisement_id);
      /** @var Advertisement $entity */
      if ($entity != null) {

         $arreglo_resultado['title'] = $entity->getTitle();
         $arreglo_resultado['description'] = $entity->getDescription();
         $arreglo_resultado['status'] = $entity->getStatus();
         $arreglo_resultado['startDate'] = $entity->getStartDate() != '' ? $entity->getStartDate()->format('m/d/Y') : '';
         $arreglo_resultado['endDate'] = $entity->getEndDate() != '' ? $entity->getEndDate()->format('m/d/Y') : '';

         $resultado['success'] = true;
         $resultado['advertisement'] = $arreglo_resultado;
      }

      return $resultado;
   }

   /**
    * EliminarAdvertisement: Elimina un rol en la BD
    * @param int $advertisement_id Id
    * @author Marcel
    */
   public function EliminarAdvertisement($advertisement_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Advertisement::class)
         ->find($advertisement_id);
      /**@var Advertisement $entity */
      if ($entity != null) {

         $advertisement_descripcion = $entity->getTitle();


         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Advertisement";
         $log_descripcion = "The advertisement is deleted: $advertisement_descripcion";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * EliminarAdvertisements: Elimina los advertisements seleccionados en la BD
    * @param int $ids Ids
    * @author Marcel
    */
   public function EliminarAdvertisements($ids)
   {
      $em = $this->getDoctrine()->getManager();

      if ($ids != "") {
         $ids = explode(',', $ids);
         $cant_eliminada = 0;
         $cant_total = 0;
         foreach ($ids as $advertisement_id) {
            if ($advertisement_id != "") {
               $cant_total++;
               $entity = $this->getDoctrine()->getRepository(Advertisement::class)
                  ->find($advertisement_id);
               /**@var Advertisement $entity */
               if ($entity != null) {

                  $advertisement_descripcion = $entity->getTitle();

                  $em->remove($entity);
                  $cant_eliminada++;

                  //Salvar log
                  $log_operacion = "Delete";
                  $log_categoria = "Advertisement";
                  $log_descripcion = "The advertisement is deleted: $advertisement_descripcion";
                  $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
               }
            }
         }
      }
      $em->flush();

      if ($cant_eliminada == 0) {
         $resultado['success'] = false;
         $resultado['error'] = "The advertisements could not be deleted, because they are associated with a project";
      } else {
         $resultado['success'] = true;

         $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected advertisements because they are associated with a project";
         $resultado['message'] = $mensaje;
      }

      return $resultado;
   }

   /**
    * ActualizarAdvertisement: Actuializa los datos del rol en la BD
    * @param int $advertisement_id Id
    * @author Marcel
    */
   public function ActualizarAdvertisement($advertisement_id, $title, $description, $status, $start_date, $end_date)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Advertisement::class)
         ->find($advertisement_id);
      /** @var Advertisement $entity */
      if ($entity != null) {

         //Verificar title
         $advertisement = $this->getDoctrine()->getRepository(Advertisement::class)
            ->findOneBy(['title' => $title]);
         if ($advertisement != null && $entity->getAdvertisementId() != $advertisement->getAdvertisementId()) {
            $resultado['success'] = false;
            $resultado['error'] = "The advertisement title is in use, please try entering another one.";
            return $resultado;
         }

         $entity->setTitle($title);
         $entity->setDescription($description);
         $entity->setStatus($status);

         if ($start_date != '') {
            $start_date = \DateTime::createFromFormat('m/d/Y', $start_date);
            $entity->setStartDate($start_date);
         }

         if ($end_date != '') {
            $end_date = \DateTime::createFromFormat('m/d/Y', $end_date);
            $entity->setEndDate($end_date);
         }

         $em->flush();

         //Salvar log
         $log_operacion = "Update";
         $log_categoria = "Advertisement";
         $log_descripcion = "The advertisement is modified: $title";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;

         return $resultado;
      }
   }

   /**
    * SalvarAdvertisement: Guarda los datos de advertisement en la BD
    * @param string $description Nombre
    * @author Marcel
    */
   public function SalvarAdvertisement($title, $description, $status, $start_date, $end_date)
   {
      $em = $this->getDoctrine()->getManager();

      //Verificar title
      $advertisement = $this->getDoctrine()->getRepository(Advertisement::class)
         ->findOneBy(['title' => $title]);
      if ($advertisement != null) {
         $resultado['success'] = false;
         $resultado['error'] = "The advertisement title is in use, please try entering another one.";
         return $resultado;
      }

      $entity = new Advertisement();

      $entity->setTitle($title);
      $entity->setDescription($description);
      $entity->setStatus($status);

      if ($start_date != '') {
         $start_date = \DateTime::createFromFormat('m/d/Y', $start_date);
         $entity->setStartDate($start_date);
      }

      if ($end_date != '') {
         $end_date = \DateTime::createFromFormat('m/d/Y', $end_date);
         $entity->setEndDate($end_date);
      }


      $em->persist($entity);

      $em->flush();

      //Salvar log
      $log_operacion = "Add";
      $log_categoria = "Advertisement";
      $log_descripcion = "The advertisement is added: $title";
      $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

      $resultado['success'] = true;

      return $resultado;
   }

   /**
    * ListarAdvertisements: Listar los advertisements
    *
    * @param int $start Inicio
    * @param int $limit Limite
    * @param string $sSearch Para buscar
    *
    * @author Marcel
    */
   public function ListarAdvertisements($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $fecha_inicial, $fecha_fin)
   {
      /** @var AdvertisementRepository $advertisementRepo */
      $advertisementRepo = $this->getDoctrine()->getRepository(Advertisement::class);
      $resultado = $advertisementRepo->ListarAdvertisementsConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $fecha_inicial, $fecha_fin);

      $data = [];

      foreach ($resultado['data'] as $value) {
         $advertisement_id = $value->getAdvertisementId();

         $data[] = array(
            "id" => $advertisement_id,
            "title" => $value->getTitle(),
            "description" => $value->getDescription(),
            "status" => $value->getStatus() ? 1 : 0,
            "startDate" => $value->getStartDate() != '' ? $value->getStartDate()->format('m/d/Y') : '',
            "endDate" => $value->getEndDate() != '' ? $value->getEndDate()->format('m/d/Y') : '',
         );
      }

      return [
         'data' => $data,
         'total' => $resultado['total'], // ya viene con el filtro aplicado
      ];
   }
}
