<?php

namespace App\Utils\Admin;

use App\Entity\DataTrackingItem;
use App\Entity\DataTrackingSubcontract;
use App\Entity\Equation;
use App\Entity\EstimateQuote;
use App\Entity\InvoiceItem;
use App\Entity\Item;
use App\Entity\DataTracking;
use App\Entity\ProjectItem;
use App\Entity\SyncQueueQbwc;
use App\Entity\Unit;
use App\Repository\DataTrackingItemRepository;
use App\Repository\DataTrackingSubcontractRepository;
use App\Repository\EstimateQuoteRepository;
use App\Repository\InvoiceItemRepository;
use App\Repository\ItemRepository;
use App\Repository\ProjectItemRepository;
use App\Repository\SyncQueueQbwcRepository;
use App\Utils\Base;

class ItemService extends Base
{

   /**
    * CargarDatosItem: Carga los datos de un item
    *
    * @param int $item_id Id
    *
    * @author Marcel
    */
   public function CargarDatosItem($item_id)
   {
      $resultado = array();
      $arreglo_resultado = array();

      $entity = $this->getDoctrine()->getRepository(Item::class)
         ->find($item_id);
      /** @var Item $entity */
      if ($entity != null) {

         $arreglo_resultado['name'] = $entity->getName();
         $arreglo_resultado['descripcion'] = $entity->getDescription();
         // $arreglo_resultado['price'] = $entity->getPrice();
         $arreglo_resultado['status'] = $entity->getStatus();
         $arreglo_resultado['unit_id'] = $entity->getUnit() != null ? $entity->getUnit()->getUnitId() : '';
         $arreglo_resultado['yield_calculation'] = $entity->getYieldCalculation();
         $arreglo_resultado['equation_id'] = $entity->getEquation() != null ? $entity->getEquation()->getEquationId() : '';

         // projects
         $projects = $this->ListarProjectsDeItem($item_id);
         $arreglo_resultado['projects'] = $projects;

         $resultado['success'] = true;
         $resultado['item'] = $arreglo_resultado;
      }

      return $resultado;
   }

   /**
    * ListarProjectsDeItem
    * @param $item_id
    * @return array
    */
   public function ListarProjectsDeItem($item_id)
   {
      $projects = [];

      /** @var ProjectItemRepository $projectItemRepo */
      $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
      $lista = $projectItemRepo->ListarProjectsDeItem($item_id);
      foreach ($lista as $key => $value) {
         $project = [
            'project_id' => $value->getProject()->getProjectId(),
            "number" => $value->getProject()->getProjectNumber(),
            "name" => $value->getProject()->getName(),
            "description" => $value->getProject()->getDescription(),
            "location" => $value->getProject()->getLocation(),
            "po_number" => $value->getProject()->getPoNumber(),
            "po_cg" => $value->getProject()->getPoCG(),
            "manager" => $value->getProject()->getManager(),
            "status" => $value->getProject()->getStatus(),
            "owner" => $value->getProject()->getOwner(),
            "county" => $value->getProject()->getCountyObj() ? $value->getProject()->getCountyObj()->getDescription() : "",
            "posicion" => $key
         ];
         $projects[] = $project;
      }

      return $projects;
   }

   /**
    * EliminarItem: Elimina un rol en la BD
    * @param int $item_id Id
    * @author Marcel
    */
   public function EliminarItem($item_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Item::class)
         ->find($item_id);
      /**@var Item $entity */
      if ($entity != null) {

         // verificar si se puede eliminar
         /*$se_puede_eliminar = $this->SePuedeEliminarItem($item_id);
            if ($se_puede_eliminar != '') {
                $resultado['success'] = false;
                $resultado['error'] = $se_puede_eliminar;
                return $resultado;
            }*/

         // eliminar informacion relacionada
         $this->EliminarInformacionDeItem($item_id);

         $item_name = $entity->getName() ?: $entity->getDescription();


         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Item";
         $log_descripcion = "The item is deleted: $item_name";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * EliminarInformacionDeItem
    * @param $item_id
    * @return void
    */
   private function EliminarInformacionDeItem($item_id)
   {
      $em = $this->getDoctrine()->getManager();

      // project items
      /** @var ProjectItemRepository $projectItemRepo */
      $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
      $project_items = $projectItemRepo->ListarProjectsDeItem($item_id);
      foreach ($project_items as $project_item) {
         $project_item_id = $project_item->getId();

         // data tracking
         /** @var DataTrackingItemRepository $dataTrackingItemRepo */
         $dataTrackingItemRepo = $this->getDoctrine()->getRepository(DataTrackingItem::class);
         $data_tracking_items = $dataTrackingItemRepo->ListarDataTrackingsDeItem($project_item_id);
         foreach ($data_tracking_items as $data_tracking_item) {
            $em->remove($data_tracking_item);
         }

         // invoices
         /** @var InvoiceItemRepository $invoiceItemRepo */
         $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
         $invoice_items = $invoiceItemRepo->ListarInvoicesDeItem($project_item_id);
         foreach ($invoice_items as $invoice_item) {
            $em->remove($invoice_item);
         }

         $em->remove($project_item);
      }

      // data tracking subcontract
      /** @var DataTrackingSubcontractRepository $dataTrackingSubcontractRepo */
      $dataTrackingSubcontractRepo = $this->getDoctrine()->getRepository(DataTrackingSubcontract::class);
      $subcontract_items = $dataTrackingSubcontractRepo->ListarSubcontractsDeItem($item_id);
      foreach ($subcontract_items as $subcontract_item) {
         $em->remove($subcontract_item);
      }

      // estimates
      /** @var EstimateQuoteRepository $estimateQuoteRepo */
      $estimateQuoteRepo = $this->getDoctrine()->getRepository(EstimateQuote::class);
      $estimate_items = $estimateQuoteRepo->ListarEstimatesDeItem($item_id);
      foreach ($estimate_items as $estimate_item) {
         $em->remove($estimate_item);
      }

      // quickbooks
      /** @var SyncQueueQbwcRepository $syncQueueQbwcRepo */
      $syncQueueQbwcRepo = $this->getDoctrine()->getRepository(SyncQueueQbwc::class);
      $quickbooks = $syncQueueQbwcRepo->ListarRegistrosDeEntidadId("item", $item_id);
      foreach ($quickbooks as $quickbook) {
         $em->remove($quickbook);
      }
   }

   /**
    * SePuedeEliminarItem
    * @param $item_id
    * @return string
    */
   private function SePuedeEliminarItem($item_id)
   {
      $texto_error = '';

      //projects
      /** @var ProjectItemRepository $projectItemRepo */
      $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
      $projects = $projectItemRepo->ListarProjectsDeItem($item_id);
      if (!empty($projects)) {
         $texto_error = "The item could not be deleted, because it has associated projects";
      }

      return $texto_error;
   }

   /**
    * EliminarItems: Elimina los items seleccionados en la BD
    * @param int $ids Ids
    * @author Marcel
    */
   public function EliminarItems($ids)
   {
      $em = $this->getDoctrine()->getManager();

      if ($ids != "") {
         $ids = explode(',', $ids);
         $cant_eliminada = 0;
         $cant_total = 0;
         foreach ($ids as $item_id) {
            if ($item_id != "") {
               $cant_total++;
               $entity = $this->getDoctrine()->getRepository(Item::class)
                  ->find($item_id);
               /**@var Item $entity */
               if ($entity != null) {

                  // eliminar informacion relacionada
                  $this->EliminarInformacionDeItem($item_id);

                  $item_name = $entity->getName() ?: $entity->getDescription();

                  $em->remove($entity);
                  $cant_eliminada++;

                  //Salvar log
                  $log_operacion = "Delete";
                  $log_categoria = "Item";
                  $log_descripcion = "The item is deleted: $item_name";
                  $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
               }
            }
         }
      }
      $em->flush();

      if ($cant_eliminada == 0) {
         $resultado['success'] = false;
         $resultado['error'] = "The items could not be deleted, because they are associated with a projects or invoices";
      } else {
         $resultado['success'] = true;

         $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected items because they are associated with a projects or invoices";
         $resultado['message'] = $mensaje;
      }

      return $resultado;
   }

   /**
    * ActualizarItem: Actuializa los datos del rol en la BD
    * @param int $item_id Id
    * @author Marcel
    */
   public function ActualizarItem($item_id, $unit_id, $name, $description, $status, $yield_calculation, $equation_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Item::class)
         ->find($item_id);
      /** @var Item $entity */
      if ($entity != null) {
         //Verificar name
         $item = $this->getDoctrine()->getRepository(Item::class)
            ->findOneBy(['name' => $name]);
         if ($item != null && $entity->getItemId() != $item->getItemId()) {
            $resultado['success'] = false;
            $resultado['error'] = "The item name is in use, please try entering another one.";
            return $resultado;
         }

         $entity->setName($name);
         $entity->setDescription($description);
         // $entity->setPrice($price);
         $entity->setStatus($status);

         $yield_calculation_old = $entity->getYieldCalculation();
         $entity->setYieldCalculation($yield_calculation);

         if ($unit_id != '') {
            $unit = $this->getDoctrine()->getRepository(Unit::class)->find($unit_id);
            $entity->setUnit($unit);
         }

         $equation_id_old = $entity->getEquation() ? $entity->getEquation()->getEquationId() : '';
         $equation = null;
         $entity->setEquation($equation);
         if ($equation_id != '') {
            $equation = $this->getDoctrine()->getRepository(Equation::class)->find($equation_id);
            $entity->setEquation($equation);
         }

         $entity->setUpdatedAt(new \DateTime());

         // actualizar en los item project
         if (($equation_id_old != $equation_id) || ($yield_calculation_old != $yield_calculation)) {
            $this->ActualizarEquationItemProjects($item_id, $yield_calculation, $equation);
         }

         // salvar en la cola
         $this->SalvarItemQuickbook($entity);

         $em->flush();

         //Salvar log
         $log_operacion = "Update";
         $log_categoria = "Item";
         $log_descripcion = "The item is modified: $name";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
         $resultado['item'] = $this->DevolverItem($entity);

         return $resultado;
      }
   }

   /**
    * ActualizarEquationItemProjects
    * @param $item_id
    * @param $equation
    * @return void
    */
   public function ActualizarEquationItemProjects($item_id, $yield_calculation, $equation)
   {
      /** @var ProjectItemRepository $projectItemRepo */
      $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
      $project_items = $projectItemRepo->ListarProjectsDeItem($item_id);
      foreach ($project_items as $project_item) {
         $project_item->setYieldCalculation($yield_calculation);
         $project_item->setEquation($equation);
      }
   }

   /**
    * SalvarItem: Guarda los datos de item en la BD
    * @param string $name Nombre
    * @param string $description Descripción
    * @author Marcel
    */
   public function SalvarItem($unit_id, $name, $description, $status, $yield_calculation, $equation_id)
   {
      $em = $this->getDoctrine()->getManager();

      //Verificar name
      $item = $this->getDoctrine()->getRepository(Item::class)
         ->findOneBy(['name' => $name]);
      if ($item != null) {
         $resultado['success'] = false;
         $resultado['error'] = "The item name is in use, please try entering another one.";
         return $resultado;
      }

      $entity = new Item();

      $entity->setName($name);
      $entity->setDescription($description);
      // $entity->setPrice($price);
      $entity->setStatus($status);
      $entity->setYieldCalculation($yield_calculation);

      if ($unit_id != '') {
         $unit = $this->getDoctrine()->getRepository(Unit::class)->find($unit_id);
         $entity->setUnit($unit);
      }

      if ($equation_id != '') {
         $equation = $this->getDoctrine()->getRepository(Equation::class)->find($equation_id);
         $entity->setEquation($equation);
      }

      $entity->setCreatedAt(new \DateTime());

      $em->persist($entity);

      $em->flush();

      // salvar en la cola
      $this->SalvarItemQuickbook($entity);

      $em->flush();

      //Salvar log
      $log_operacion = "Add";
      $log_categoria = "Item";
      $log_descripcion = "The item is added: $name";
      $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

      $resultado['success'] = true;
      $resultado['item'] = $this->DevolverItem($entity);

      return $resultado;
   }

   /**
    * SalvarItemQuickbook
    * @param Item $entity
    * @return void
    */
   public function SalvarItemQuickbook($entity)
   {
      $em = $this->getDoctrine()->getManager();

      $item_id = $entity->getItemId();

      $sync_queue_qbwc = $this->getDoctrine()->getRepository(SyncQueueQbwc::class)
         ->findOneBy(['tipo' => 'item', 'entidadId' => $item_id]);
      $is_new_sync_queue_qbwc = false;
      if ($sync_queue_qbwc == null) {
         $sync_queue_qbwc = new SyncQueueQbwc();
         $is_new_sync_queue_qbwc = true;
      }

      $sync_queue_qbwc->setEstado('pendiente');

      if ($is_new_sync_queue_qbwc) {
         $sync_queue_qbwc->setTipo('item');
         $sync_queue_qbwc->setEntidadId($item_id);
         $sync_queue_qbwc->setIntentos(0);

         $sync_queue_qbwc->setCreatedAt(new \DateTime());

         $em->persist($sync_queue_qbwc);
      }
   }

   /**
    * ListarItems: Listar los items
    *
    * @param int $start Inicio
    * @param int $limit Limite
    * @param string $sSearch Para buscar
    *
    * @author Marcel
    */
   public function ListarItems($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
   {
      /** @var ItemRepository $itemRepo */
      $itemRepo = $this->getDoctrine()->getRepository(Item::class);
      $resultado = $itemRepo->ListarItemsConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

      $data = [];

      foreach ($resultado['data'] as $value) {
         $item_id = $value->getItemId();

         $yield_calculation = $this->DevolverYieldCalculationDeItem($value);

         $data[] = array(
            "id" => $item_id,
            "name" => $value->getName(),
            "description" => $value->getDescription(),
            // "price" => number_format($value->getPrice(), 2, '.', ','),
            "status" => $value->getStatus() ? 1 : 0,
            "unit" => $value->getUnit() != null ? $value->getUnit()->getDescription() : '',
            "yieldCalculation" => $yield_calculation,
         );
      }

      return [
         'data' => $data,
         'total' => $resultado['total'], // ya viene con el filtro aplicado
      ];
   }

   /**
    * TotalItems: Total de items
    * @param string $sSearch Para buscar
    * @author Marcel
    */
   public function TotalItems($sSearch)
   {
      /** @var ItemRepository $itemRepo */
      $itemRepo = $this->getDoctrine()->getRepository(Item::class);
      $total = $itemRepo->TotalItems($sSearch);

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
      $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 6);

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
