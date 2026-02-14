<?php

namespace App\Utils\Admin;

use App\Entity\Company;
use App\Entity\CompanyContact;
use App\Entity\ConcreteClass;
use App\Entity\ConcreteVendor;
use App\Entity\County;
use App\Entity\DataTrackingConcVendor;
use App\Entity\DataTrackingItem;
use App\Entity\DataTrackingLabor;
use App\Entity\DataTrackingMaterial;
use App\Entity\DataTrackingSubcontract;
use App\Entity\Equation;
use App\Entity\Inspector;
use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\Item;
use App\Entity\Notification;
use App\Entity\Project;
use App\Entity\DataTracking;
use App\Entity\ProjectAttachment;
use App\Entity\ProjectContact;
use App\Entity\ProjectCounty;
use App\Entity\ProjectConcreteClass;
use App\Entity\ProjectItem;
use App\Entity\ProjectItemHistory;
use App\Entity\ProjectNotes;
use App\Entity\ProjectPriceAdjustment;
use App\Entity\Schedule;
use App\Entity\ScheduleConcreteVendorContact;
use App\Entity\ScheduleEmployee;
use App\Entity\SyncQueueQbwc;
use App\Entity\Unit;
use App\Repository\DataTrackingConcVendorRepository;
use App\Repository\DataTrackingItemRepository;
use App\Repository\DataTrackingLaborRepository;
use App\Repository\DataTrackingMaterialRepository;
use App\Repository\DataTrackingRepository;
use App\Repository\DataTrackingSubcontractRepository;
use App\Repository\InvoiceItemRepository;
use App\Repository\InvoiceRepository;
use App\Repository\NotificationRepository;
use App\Repository\ProjectAttachmentRepository;
use App\Repository\ProjectContactRepository;
use App\Repository\ProjectItemHistoryRepository;
use App\Repository\ProjectItemRepository;
use App\Repository\ProjectNotesRepository;
use App\Repository\ProjectPriceAdjustmentRepository;
use App\Repository\ProjectRepository;
use App\Repository\ProjectCountyRepository;
use App\Repository\ProjectConcreteClassRepository;
use App\Repository\ScheduleConcreteVendorContactRepository;
use App\Repository\ScheduleEmployeeRepository;
use App\Repository\ScheduleRepository;
use App\Repository\EmployeeRoleRepository;
use App\Entity\EmployeeRole;

use App\Utils\Admin\InvoiceService;
use App\Utils\Base;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Mailer\MailerInterface;

class ProjectService extends Base
{
   /** @var InvoiceService */
   private $invoiceService;

   public function __construct(
      ContainerInterface $container,
      MailerInterface $mailer,
      ContainerBagInterface $containerBag,
      Security $security,
      LoggerInterface $logger,
      InvoiceService $invoiceService
   ) {
      parent::__construct($container, $mailer, $containerBag, $security, $logger);
      $this->invoiceService = $invoiceService;
   }

   /**
    * EliminarArchivos: Elimina varios archivos en la BD
    *
    * @param $archivos
    * @return array
    */
   public function EliminarArchivos($archivos)
   {
      $resultado = array();

      $archivos = explode(',', $archivos);
      foreach ($archivos as $archivo) {
         //Eliminar archivo
         $dir = 'uploads/project/';
         if (is_file($dir . $archivo)) {
            unlink($dir . $archivo);
         }

         $em = $this->getDoctrine()->getManager();

         $archivo_entity = $this->getDoctrine()->getRepository(ProjectAttachment::class)
            ->findOneBy(array('file' => $archivo));
         if ($archivo_entity != null) {
            $em->remove($archivo_entity);
         }
      }

      $em->flush();

      $resultado['success'] = true;
      return $resultado;
   }

   /**
    * EliminarArchivo: Elimina un archivo en la BD
    *
    * @param $archivo
    * @return array
    */
   public function EliminarArchivo($archivo)
   {
      $resultado = array();

      //Eliminar archivo
      $dir = 'uploads/project/';
      if (is_file($dir . $archivo)) {
         unlink($dir . $archivo);
      }

      $em = $this->getDoctrine()->getManager();

      $archivo_entity = $this->getDoctrine()->getRepository(ProjectAttachment::class)
         ->findOneBy(array('file' => $archivo));
      if ($archivo_entity != null) {
         $em->remove($archivo_entity);
      }

      $em->flush();

      $resultado['success'] = true;
      return $resultado;
   }

   /**
    * EliminarAjustePrecio: Elimina un ajuste de precio en la BD
    * @param int $id Id
    * @author Marcel
    */
   public function EliminarAjustePrecio($id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(ProjectPriceAdjustment::class)
         ->find($id);
      /**@var ProjectPriceAdjustment $entity */
      if ($entity != null) {

         $project = $entity->getProject()->getProjectNumber();
         $day = $entity->getDay()->format('m/d/Y');
         $percent = $entity->getPercent();

         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Project Escalator";
         $log_descripcion = "The project escalator is deleted: Project #: $project, Day: $day, Percent: $percent";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * ListarDataTrackings: Listar los items details
    *
    * @param int $start Inicio
    * @param int $limit Limite
    * @param string $sSearch Para buscar
    *
    * @author Marcel
    */
   public function ListarDataTrackings($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $fecha_inicial, $fecha_fin, $pending)
   {
      /** @var DataTrackingRepository $dataTrackingRepo */
      $dataTrackingRepo = $this->getDoctrine()->getRepository(DataTracking::class);
      $resultado = $dataTrackingRepo->ListarDataTrackingsConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $fecha_inicial, $fecha_fin, $pending);

      $data = [];

      foreach ($resultado['data'] as $value) {
         $data_tracking_id = $value->getId();

         // conc vendor
         /** @var DataTrackingConcVendorRepository $dataTrackingConcVendorRepo */
         $dataTrackingConcVendorRepo = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class);
         $total_conc_used = $dataTrackingConcVendorRepo->TotalConcUsed($data_tracking_id);

         $total_concrete_yiel = $this->CalcularTotalConcreteYiel($data_tracking_id);

         $lost_concrete = round($total_conc_used - $total_concrete_yiel, 2);

         // totales

         /*$total_quantity_today = $this->getDoctrine()->getRepository(DataTrackingItem::class)
                ->TotalQuantity($data_tracking_id);*/
         $total_quantity_today = $total_conc_used;

         /** @var DataTrackingItemRepository $dataTrackingItemRepo */
         $dataTrackingItemRepo = $this->getDoctrine()->getRepository(DataTrackingItem::class);
         $total_daily_today = $dataTrackingItemRepo->TotalDaily($data_tracking_id);

         /** @var DataTrackingSubcontractRepository $dataTrackingSubcontractRepo */
         $dataTrackingSubcontractRepo = $this->getDoctrine()->getRepository(DataTrackingSubcontract::class);
         $total_subcontract = $dataTrackingSubcontractRepo->TotalPrice($data_tracking_id);

         $total_daily_today = $total_daily_today - $total_subcontract;


         // concrete used price
         /** @var DataTrackingConcVendorRepository $dataTrackingConcVendorRepo */
         $dataTrackingConcVendorRepo = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class);
         $total_concrete = $dataTrackingConcVendorRepo->TotalConcPrice($data_tracking_id);


         /** @var DataTrackingLaborRepository $dataTrackingLaborRepo */
         $dataTrackingLaborRepo = $this->getDoctrine()->getRepository(DataTrackingLabor::class);
         $total_labor = $dataTrackingLaborRepo->TotalLabor($data_tracking_id);

         /** @var DataTrackingMaterialRepository $dataTrackingMaterialRepo */
         $dataTrackingMaterialRepo = $this->getDoctrine()->getRepository(DataTrackingMaterial::class);
         $total_material = $dataTrackingMaterialRepo->TotalMaterials($data_tracking_id);

         $total_people = $value->getTotalPeople();
         $overhead_price = $value->getOverheadPrice();
         $total_overhead = $total_people * $overhead_price;

         // "Labor Total" is the sum of Labor and Overhead Totals
         $total_labor = $total_labor + $total_overhead;

         $profit = $total_daily_today - ($total_concrete + $total_labor + $total_material);

         // color
         $color_used = $value->getColorUsed();
         $color_price = $value->getColorPrice();
         $total_color = $color_used * $color_price;

         $pending = $value->getPending() ? 1 : 0;

         $leads = $this->ListarLeadsDeDataTracking($data_tracking_id);

         $data[] = [
            "id" => $data_tracking_id,
            'project' => $value->getProject()->getProjectNumber() . " - " . $value->getProject()->getDescription(),
            'date' => $value->getDate()->format('m/d/Y'),
            "stationNumber" => $value->getStationNumber(),
            "measuredBy" => $value->getMeasuredBy(),
            "totalConcUsed" => $total_conc_used,
            "lostConcrete" => $lost_concrete,
            "concVendor" => $value->getConcVendor(),
            "concPrice" => $value->getConcPrice(),
            "inspector" => $value->getInspector() != null ? $value->getInspector()->getName() : '',
            "inspectorNumber" => $value->getInspector() != null ? $value->getInspector()->getPhone() : '',
            "crewLead" => $value->getCrewLead(),
            "notes" => $value->getNotes(),
            "totalLabor" => $total_labor,
            "totalMaterial" => $total_material,
            "totalStamps" => $value->getTotalStamps(),
            "otherMaterials" => $value->getOtherMaterials(),
            "leads" => $leads,
            // overhead
            "totalPeople" => $total_people,
            "overheadPrice" => $overhead_price,
            "totalOverhead" => $total_overhead,
            // color
            "colorUsed" => $color_used,
            "colorPrice" => $color_price,
            "totalColor" => $total_color,
            // totales
            "total_concrete_yiel" => $total_concrete_yiel,
            'total_quantity_today' => $total_quantity_today != null ? $total_quantity_today : 0,
            'total_daily_today' => $total_daily_today,
            'total_concrete' => $total_concrete,
            'profit' => $profit,
            'pending' => $pending,
         ];
      }

      return [
         'data' => $data,
         'total' => $resultado['total'],
      ];
   }

   /**
    * ListarLeadsDeDataTracking
    * @param $data_tracking_id
    * @return array
    */
   private function ListarLeadsDeDataTracking($data_tracking_id)
   {
      $items = [];

      /** @var DataTrackingLaborRepository $dataTrackingLaborRepo */
      $dataTrackingLaborRepo = $this->getDoctrine()->getRepository(DataTrackingLabor::class);
      $lista = $dataTrackingLaborRepo->ListarLabor($data_tracking_id);
      foreach ($lista as $key => $value) {

         if ($value->getRole() === 'Lead' && ($value->getEmployee() !== null || $value->getEmployeeSubcontractor() !== null)) {
            $employee_name = $value->getEmployee() !== null ? $value->getEmployee()->getName() : $value->getEmployeeSubcontractor()->getName();
            $items[] = $employee_name;
         }
      }

      return implode(",", $items);
   }

   /**
    * ListarEmployees
    * @param $project_id
    * @return array
    */
   public function ListarEmployees($project_id)
   {
      $employees = [];

      /** @var DataTrackingLaborRepository $dataTrackingLaborRepo */
      $dataTrackingLaborRepo = $this->getDoctrine()->getRepository(DataTrackingLabor::class);
      $project_employees = $dataTrackingLaborRepo->ListarEmployeesDeProject($project_id);

      foreach ($project_employees as $key => $project_employee) {
         $value = $project_employee->getEmployee();

         $employees[] = [
            "employee_id" => $value->getEmployeeId(),
            "name" => $value->getName(),
            'posicion' => $key
         ];
      }

      return $employees;
   }

   /**
    * ListarSubcontractors
    * @param $project_id
    * @return array
    */
   public function ListarSubcontractors($project_id)
   {
      $subcontractors = [];

      /** @var DataTrackingSubcontractRepository $dataTrackingSubcontractRepo */
      $dataTrackingSubcontractRepo = $this->getDoctrine()->getRepository(DataTrackingSubcontract::class);
      $project_subcontractors = $dataTrackingSubcontractRepo->ListarSubcontractorsDeProject($project_id);

      foreach ($project_subcontractors as $key => $project_subcontractor) {
         $value = $project_subcontractor->getSubcontractor();
         if ($value) {
            $subcontractors[] = [
               "subcontractor_id" => $value->getSubcontractorId(),
               "name" => $value->getName(),
               "phone" => $value->getPhone(),
               "address" => $value->getAddress(),
               "contactName" => $value->getContactName(),
               "contactEmail" => $value->getContactEmail(),
               "companyName" => $value->getCompanyName(),
               "companyPhone" => $value->getCompanyPhone(),
               "companyAddress" => $value->getCompanyAddress(),
               'posicion' => $key
            ];
         }
      }

      return $subcontractors;
   }

   /**
    * EliminarContact: Elimina un contact en la BD
    * @param int $contact_id Id
    * @author Marcel
    */
   public function EliminarContact($contact_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(ProjectContact::class)
         ->find($contact_id);
      /**@var ProjectContact $entity */
      if ($entity != null) {

         $contact_name = $entity->getName();

         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Contact";
         $log_descripcion = "The project contact is deleted: $contact_name";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * EliminarConcreteClass: Elimina una concrete class en la BD
    * @param int $concrete_class_id Id
    * @author Marcel
    */
   public function EliminarConcreteClass($concrete_class_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(ProjectConcreteClass::class)
         ->find($concrete_class_id);
      /**@var ProjectConcreteClass $entity */
      if ($entity != null) {

         $concrete_class_name = $entity->getConcreteClass() ? $entity->getConcreteClass()->getName() : '';

         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Concrete Class";
         $log_descripcion = "The project concrete class is deleted: $concrete_class_name";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * AgregarItem
    * @param $item_id
    * @param $item_name
    * @param $unit_id
    * @param $quantity
    * @param $price
    * @param $yield_calculation
    * @param $equation_id
    * @return array
    */
   public function AgregarItem($project_item_id, $project_id, $item_id, $item_name, $unit_id, $quantity, $price, $yield_calculation, $equation_id, $change_order, $change_order_date, $apply_retainage, $bond = false, $bonded = false)
   {
      $resultado = [];

      $em = $this->getDoctrine()->getManager();

      // validar si existe
      if ($item_id !== '') {
         /** @var ProjectItemRepository $projectItemRepo */
         $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
         $project_item = $projectItemRepo->BuscarItemProject($project_id, $item_id, $price);
         if (!empty($project_item) && $project_item_id != $project_item[0]->getId()) {
            $resultado['success'] = false;
            $resultado['error'] = "The item already exists in the project";
            return $resultado;
         }
      } else {
         //Verificar name
         $item = $this->getDoctrine()->getRepository(Item::class)
            ->findOneBy(['name' => $item_name]);
         if ($item_id == '' && $item != null) {
            $resultado['success'] = false;
            $resultado['error'] = "The item name is in use, please try entering another one.";
            return $resultado;
         }
      }


      $project_entity = $this->getDoctrine()->getRepository(Project::class)->find($project_id);
      if ($project_entity != null) {
         // para las notas
         $notas = [];

         $project_item_entity = null;

         if (is_numeric($project_item_id)) {
            $project_item_entity = $this->getDoctrine()->getRepository(ProjectItem::class)
               ->find($project_item_id);
         }

         $is_new_project_item = false;
         if ($project_item_entity == null) {
            $project_item_entity = new ProjectItem();
            $is_new_project_item = true;
         }

         $project_item_entity->setApplyRetainage($apply_retainage);
         $project_item_entity->setBonded($bonded);

         $project_item_entity->setYieldCalculation($yield_calculation);

         $price_old = $project_item_entity->getPrice();
         $project_item_entity->setPrice($price);

         $quantity_old = $project_item_entity->getQuantity();
         $project_item_entity->setQuantity($quantity);

         // Verificar si se está desactivando el change order
         $change_order_old = $project_item_entity->getChangeOrder();
         $project_item_entity->setChangeOrder($change_order);

         if ($change_order) {
            // Si se activa el change order, establecer la fecha si se proporciona
            if ($change_order_date != '') {
               $change_order_date = \DateTime::createFromFormat('m/d/Y', $change_order_date);
               $project_item_entity->setChangeOrderDate($change_order_date);
            }
         } else {
            // Si se desactiva el change order, establecer la fecha en null y eliminar el historial
            $project_item_entity->setChangeOrderDate(null);
            if ($change_order_old && $project_item_entity->getId()) {
               // Eliminar historial solo si antes estaba activo
               $this->EliminarHistorialDeProjectItem($project_item_entity->getId());
            }
         }

         $equation_entity = null;
         if ($equation_id != '') {
            $equation_entity = $this->getDoctrine()->getRepository(Equation::class)->find($equation_id);
            $project_item_entity->setEquation($equation_entity);
         }

         $is_new_item = false;
         if ($item_id != '') {
            $item_entity = $this->getDoctrine()->getRepository(Item::class)->find($item_id);
            // Actualizar bond del item del catálogo cuando el usuario con permiso bond lo modifica desde el proyecto
            $item_entity->setBond($bond);
         } else {
            // add new item
            $new_item_data = json_encode([
               'item' => $item_name,
               'price' => $price,
               'yield_calculation' => $yield_calculation,
               'unit_id' => $unit_id,
               'bond' => $bond
            ]);
            $item_entity = $this->AgregarNewItem(json_decode($new_item_data), $equation_entity);

            $is_new_item = true;
         }

         $item_description = $item_entity->getName();
         $project_item_entity->setItem($item_entity);

         if ($is_new_project_item) {

            // marcar principal
            /** @var ProjectItemRepository $projectItemRepo */
            $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
            $project_items = $projectItemRepo->BuscarItemProject($project_id, $item_id);
            $principal = empty($project_items) ? true : false;
            $project_item_entity->setPrincipal($principal);

            $project_item_entity->setProject($project_entity);

            $em->persist($project_item_entity);

            // registrar nota
            $notas[] = [
               'notes' => "Add New Item: {$item_description}",
               'date' => new \DateTime()
            ];
         } else {

            // change price
            if ($price_old != $price) {

               $project_item_entity->setPriceOld($price_old);

               $notas[] = [
                  'notes' => "Change Price Item: {$item_description}, Previous Price: {$price_old}, New Price: {$price}",
                  'date' => new \DateTime()
               ];
            }

            // change quantity
            if ($quantity_old != $quantity) {

               $project_item_entity->setQuantityOld($quantity_old);

               $notas[] = [
                  'notes' => "Change Quantity Item: {$item_description}, Previous Quantity: {$quantity_old}, New Quantity: {$quantity}",
                  'date' => new \DateTime()
               ];
            }
         }

         $this->SalvarNotesUpdate($project_entity, $notas);

         // Si es un nuevo item con change order, hacer flush para obtener el ID
         if ($change_order === true && $is_new_project_item) {
            $em->flush();
            // Refrescar la entidad para asegurar que tenga el ID
            $em->refresh($project_item_entity);
         }

         // Registrar historial: para change order (add + cambios), para el resto solo cambios de cantidad/precio
         if ($change_order === true) {
            $is_first_time_change_order = !$change_order_old && $change_order;
            $this->RegistrarHistorialChangeOrder($project_item_entity, $is_new_project_item, $is_first_time_change_order, $quantity_old, $quantity, $price_old, $price);
         } elseif (!$is_new_project_item && ($quantity_old != $quantity || $price_old != $price)) {
            // Item no es change order: registrar solo historial de cantidad/precio si cambiaron
            $this->RegistrarHistorialChangeOrder($project_item_entity, false, false, $quantity_old, $quantity, $price_old, $price);
         }

         $em->flush();

         $resultado['success'] = true;

         // devolver item
         $item = $this->DevolverItemDeProject($project_item_entity);
         $resultado['item'] = $item;
         $resultado['is_new_item'] = $is_new_item;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = 'The project not exist';
      }

      return $resultado;
   }

   /**
    * EliminarItem: Elimina un item en la BD
    * @param int $project_item_id Id
    * @author Marcel
    */
   public function EliminarItem($project_item_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(ProjectItem::class)
         ->find($project_item_id);
      /**@var ProjectItem $entity */
      if ($entity != null) {

         // verificar si se puede eliminar
         /*$se_puede_eliminar = $this->SePuedeEliminarItem($project_item_id);
            if ($se_puede_eliminar != '') {
                $resultado['success'] = false;
                $resultado['error'] = $se_puede_eliminar;
                return $resultado;
            }*/

         // eliminar informacion relacionada
         $this->EliminarInformacionDeProjectItem($project_item_id);

         $item_name = $entity->getItem()->getName();

         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Project Item";
         $log_descripcion = "The item: $item_name of the project is deleted";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * EliminarInformacionDeProjectItem
    * @param $project_item_id
    * @return void
    */
   private function EliminarInformacionDeProjectItem($project_item_id)
   {
      $em = $this->getDoctrine()->getManager();

      // data tracking
      /** @var DataTrackingItemRepository $dataTrackingItemRepo */
      $dataTrackingItemRepo = $this->getDoctrine()->getRepository(DataTrackingItem::class);
      $data_tracking_items = $dataTrackingItemRepo->ListarDataTrackingsDeItem($project_item_id);
      foreach ($data_tracking_items as $data_tracking_item) {
         $em->remove($data_tracking_item);
      }

      // subcontractors
      /** @var DataTrackingSubcontractRepository $dataTrackingSubcontractRepo */
      $dataTrackingSubcontractRepo = $this->getDoctrine()->getRepository(DataTrackingSubcontract::class);
      $data_tracking_subcontractors = $dataTrackingSubcontractRepo->ListarSubcontractsDeItemProject($project_item_id);
      foreach ($data_tracking_subcontractors as $data_tracking_subcontractor) {
         $em->remove($data_tracking_subcontractor);
      }

      // invoices
      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
      $invoice_items = $invoiceItemRepo->ListarInvoicesDeItem($project_item_id);
      foreach ($invoice_items as $invoice_item) {
         $em->remove($invoice_item);
      }

      // project item history
      $this->EliminarHistorialDeProjectItem($project_item_id);
   }

   /**
    * EliminarHistorialDeProjectItem: Elimina solo el historial de un ProjectItem
    * @param $project_item_id
    * @return void
    */
   private function EliminarHistorialDeProjectItem($project_item_id)
   {
      $em = $this->getDoctrine()->getManager();

      // project item history
      /** @var ProjectItemHistoryRepository $historyRepo */
      $historyRepo = $this->getDoctrine()->getRepository(ProjectItemHistory::class);
      $historial = $historyRepo->ListarHistorialDeItem($project_item_id);
      foreach ($historial as $historial_item) {
         $em->remove($historial_item);
      }
   }

   /**
    * SePuedeEliminarItem
    * @param $item_id
    * @return string
    */
   private function SePuedeEliminarItem($project_item_id)
   {
      $texto_error = '';

      // data tracking
      /** @var DataTrackingItemRepository $dataTrackingItemRepo */
      $dataTrackingItemRepo = $this->getDoctrine()->getRepository(DataTrackingItem::class);
      $data_tracking = $dataTrackingItemRepo->ListarDataTrackingsDeItem($project_item_id);
      if (count($data_tracking) > 0) {
         $texto_error = "The item could not be deleted, because it is related to a data tracking";
      }

      // invoices
      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
      $invoices = $invoiceItemRepo->ListarInvoicesDeItem($project_item_id);
      if (count($invoices) > 0) {
         $texto_error = "The item could not be deleted, because it is related to a invoice";
      }

      return $texto_error;
   }

   /**
    * EliminarNotes: Elimina un notes en la BD
    * @param int $notes_id Id
    * @author Marcel
    */
   public function EliminarNotes($notes_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(ProjectNotes::class)
         ->find($notes_id);
      /**@var ProjectNotes $entity */
      if ($entity != null) {
         $notes = $entity->getNotes();
         $project_name = $entity->getProject()->getName();

         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Project Notes";
         $log_descripcion = "The notes: $notes is delete from project: $project_name";
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
    * @param int $project_id Id
    * @author Marcel
    */
   public function EliminarNotesDate($project_id, $from, $to)
   {
      $em = $this->getDoctrine()->getManager();

      $project_entity = $this->getDoctrine()->getRepository(Project::class)
         ->find($project_id);
      /** @var Project $project_entity */
      if ($project_entity != null) {

         $project_name = $project_entity->getName();


         /** @var ProjectNotesRepository $projectNotesRepo */
         $projectNotesRepo = $this->getDoctrine()->getRepository(ProjectNotes::class);
         $notes = $projectNotesRepo->ListarNotesDeProject($project_id, $from, $to);
         foreach ($notes as $entity) {
            $em->remove($entity);
         }

         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Project Notes";
         $log_descripcion = "The notes $from and $to is delete from project: $project_name";
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

      $entity = $this->getDoctrine()->getRepository(ProjectNotes::class)
         ->find($notes_id);
      /** @var ProjectNotes $entity */
      if ($entity != null) {

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
    * @param $project_id
    * @param $notes
    * @param $date
    * @return array
    */
   public function SalvarNotes($notes_id, $project_id, $notes, $date)
   {

      $em = $this->getDoctrine()->getManager();

      $project_entity = $this->getDoctrine()->getRepository(Project::class)
         ->find($project_id);
      /** @var Project $project_entity */
      if ($project_entity != null) {

         $entity = null;
         $is_new = false;

         if (is_numeric($notes_id)) {
            $entity = $this->getDoctrine()->getRepository(ProjectNotes::class)
               ->find($notes_id);
         }

         if ($entity == null) {
            $entity = new ProjectNotes();
            $is_new = true;
         }

         $entity->setNotes($notes);

         if ($date != '') {
            $date = \DateTime::createFromFormat('m/d/Y', $date);
            $entity->setDate($date);
         }

         $entity->setProject($project_entity);

         $log_operacion = "Add";
         $log_descripcion = "The notes: $notes is add to the project: " . $project_entity->getName();

         if ($is_new) {
            $em->persist($entity);
         } else {
            $log_operacion = "Update";
            $log_descripcion = "The notes: $notes is modified to the project: " . $project_entity->getName();
         }

         $em->flush();

         //Salvar log
         $log_categoria = "Project Notes";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The project not exist.";
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
   public function ListarNotes($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $fecha_inicial, $fecha_fin)
   {
      /** @var ProjectNotesRepository $projectNotesRepo */
      $projectNotesRepo = $this->getDoctrine()->getRepository(ProjectNotes::class);
      $resultado = $projectNotesRepo->ListarNotesConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $fecha_inicial, $fecha_fin);

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
    * TotalNotes: Total de notes
    * @param string $sSearch Para buscar
    * @author Marcel
    */
   public function TotalNotes($sSearch, $project_id, $fecha_inicial, $fecha_fin)
   {
      /** @var ProjectNotesRepository $projectNotesRepo */
      $projectNotesRepo = $this->getDoctrine()->getRepository(ProjectNotes::class);
      $total = $projectNotesRepo->TotalNotes($sSearch, $project_id, $fecha_inicial, $fecha_fin);

      return $total;
   }

   /**
    * ListarAccionesNotes: Lista las acciones
    *
    * @author Marcel
    */
   public function ListarAccionesNotes($id)
   {
      $usuario = $this->getUser();
      $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 9);

      $acciones = "";

      if (count($permiso) > 0) {
         if ($permiso[0]['editar']) {
            $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="Edit record" data-id="' . $id . '"> <i class="la la-edit"></i> </a> ';
            $acciones .= ' <a href="javascript:;" class="delete m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete record" data-id="' . $id . '"><i class="la la-trash"></i></a>';
         } else {
            $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="View record" data-id="' . $id . '"> <i class="la la-eye"></i> </a> ';
         }
      }

      return $acciones;
   }

   /**
    * ListarOrdenados
    * @param $search
    * @param $company_id
    * @param $inspector_id
    * @return array
    */
   public function ListarOrdenados($search = '', $company_id = '', $inspector_id = '', $from = '', $to = '', $status = '')
   {
      $projects = [];

      /** @var ProjectRepository $projectRepo */
      $projectRepo = $this->getDoctrine()->getRepository(Project::class);
      $lista = $projectRepo->ListarOrdenados($search, $company_id, $inspector_id, $from, $to);
      foreach ($lista as $value) {
         $project_id = $value->getProjectId();

         $is_valid_status = $this->FiltrarProjectPorStatus($project_id, $status);
         if ($is_valid_status) {
            $projects[] = [
               'project_id' => $project_id,
               'number' => $value->getProjectNumber(),
               'name' => $value->getName(),
               'description' => $value->getDescription(),
               'invoice_due_date' => $value->getDueDate() != null ? $value->getDueDate()->format('m/d/Y') : '',
            ];
         }
      }

      return $projects;
   }

   /**
    * FiltrarProjectPorStatus
    * @param $status
    * @return boolean
    */
   private function FiltrarProjectPorStatus($project_id, $status)
   {
      $is_valid = true;

      if ($status != '') {

         $is_valid = false;

         /** @var DataTrackingRepository $dataTrackingRepo */
         $dataTrackingRepo = $this->getDoctrine()->getRepository(DataTracking::class);
         $data_tracking = $dataTrackingRepo->ListarDataTracking($project_id);

         if ($status == 'working' && !empty($data_tracking)) {
            $is_valid = true;
         }
         if ($status == 'notworking' && empty($data_tracking)) {
            $is_valid = true;
         }
      }


      return $is_valid;
   }

   /**
    * ListarItemsParaInvoice
    * @param $project_id
    * @param $fecha_inicial
    * @param $fecha_fin
    * @return array
    */
   public function ListarItemsParaInvoice($project_id, $fecha_inicial, $fecha_fin)
   {
      $items = [];

      // listar items de project
      /** @var ProjectItemRepository $projectItemRepo */
      $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
      $project_items = $projectItemRepo->ListarItemsDeProject($project_id);
      foreach ($project_items as $value) {
         // El ítem marcado como Bond no va en la tabla del invoice; solo se incluye en el Excel
         if ($value->getItem()->getBond()) {
            continue;
         }
         $project_item_id = $value->getId();

         /** @var DataTrackingItemRepository $dataTrackingItemRepo */
         $dataTrackingItemRepo = $this->getDoctrine()->getRepository(DataTrackingItem::class);
         $quantity = $dataTrackingItemRepo->TotalQuantity("", $project_item_id, $fecha_inicial, $fecha_fin);

         // Verificar si hay invoices anteriores para determinar si es el primer invoice
         /** @var InvoiceItemRepository $invoiceItemRepo */
         $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
         $quantity_from_previous = $invoiceItemRepo->TotalPreviousQuantity($project_item_id);

         // Si quantity_from_previous == 0, no hay invoices anteriores, entonces es el primer invoice
         $isFirstInvoice = ($quantity_from_previous == 0);

         // calcular unpaid_qty (suma de unpaid_qty de invoices anteriores)
         $unpaid_qty = $this->CalcularUnpaidQuantityFromPreviusInvoice($project_item_id);

         $contract_qty = $value->getQuantity();
         $price = $value->getPrice();
         $contract_amount = $contract_qty * $price;

         $amount_from_previous = $invoiceItemRepo->TotalPreviousAmount($project_item_id);

         $quantity_completed = $quantity + $quantity_from_previous;

         $amount = $quantity * $price;

         $total_amount = $quantity_completed * $price;
         $amount_completed = $total_amount;

         $paid_amount_total = $this->CalculaPaidAmountTotalFromPreviusInvoice($project_item_id);


         $quantity_brought_forward = 0;
         // quantity_final = quantity + quantity_brought_forward (Invoice Qty)
         $quantity_final = $quantity + $quantity_brought_forward;
         $amount_final = $quantity_final * $price;

         // unpaid_qty = suma de los unpaid_qty de todos los invoices anteriores
         // Se calcula arriba con CalcularUnpaidQuantityFromPreviusInvoice

         // Calcular amount_unpaid
         $amount_unpaid = $unpaid_qty * $price;

         // Verificar si hay historial de cantidad y precio
         /** @var ProjectItemHistoryRepository $historyRepo */
         $historyRepo = $this->getDoctrine()->getRepository(ProjectItemHistory::class);
         $has_quantity_history = $historyRepo->TieneHistorialCantidad($project_item_id);
         $has_price_history = $historyRepo->TieneHistorialPrecio($project_item_id);

         // Preparar datos para el frontend
         $item_data = [
            "project_item_id" => $project_item_id,
            "apply_retainage" => $value->getApplyRetainage(),
            "bonded" => $value->getBonded() ? 1 : 0,
            "bond" => $value->getItem()->getBond() ? 1 : 0,
            "item_id" => $value->getItem()->getItemId(),
            "item" => $value->getItem()->getName(),
            "unit" => $value->getItem()->getUnit() != null ? $value->getItem()->getUnit()->getDescription() : '',
            "contract_qty" => $contract_qty,
            "price" => $price,
            "contract_amount" => $contract_amount,
            "quantity_from_previous" => $quantity_from_previous ?? 0,
            "unpaid_qty" => $unpaid_qty,
            "quantity" => $quantity ?? 0,
            "quantity_completed" => $quantity_completed,
            "amount" => $amount,
            "total_amount" => $total_amount,
            "paid_amount_total" => $paid_amount_total,
            "amount_from_previous" => $amount_from_previous,
            "amount_completed" => $amount_completed,

            "quantity_brought_forward" => $quantity_brought_forward,
            "quantity_final" => $quantity_final,
            "amount_final" => $amount_final,
            "unpaid_amount" => $amount_unpaid,
            "principal" => $value->getPrincipal(),
            "change_order" => $value->getChangeOrder(),
            "change_order_date" => $value->getChangeOrderDate() != null ? $value->getChangeOrderDate()->format('m/d/Y') : '',
            "has_quantity_history" => $has_quantity_history,
            "has_price_history" => $has_price_history,
         ];

         $items[] = $item_data;
      }

      // Calcular SUM_BONDED_PROJECT, Bond Price y Bond General para que JavaScript pueda calcular X e Y
      /** @var ProjectItemRepository $projectItemRepo */
      $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
      $sum_bonded_project = $projectItemRepo->TotalBondedProjectItems($project_id);
      $bond_price = $projectItemRepo->TotalBondPriceProjectItems($project_id);
      $bon_general = $projectItemRepo->TotalBondAmountProjectItems($project_id);

      // Bond disponible para este invoice nuevo: 1 - usado (invoices con start_date <= fecha_inicial)
      /** @var \App\Repository\InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(\App\Entity\Invoice::class);
      $bon_quantity_used_before = (float) $invoiceRepo->SumBonQuantityUsedBeforeOrOnDate($project_id, $fecha_inicial);
      $bon_quantity_available = max(0.0, 1.0 - $bon_quantity_used_before);

      // Calcular bon_quantity y bon_amount con la misma lógica que RecalcularBonProyecto (frontend no calcula nada)
      $sum_bonded_invoices = 0.0;
      foreach ($items as $it) {
         if (!empty($it['bonded'])) {
            $qty = (float) ($it['quantity'] ?? 0);
            $qbf = (float) ($it['quantity_brought_forward'] ?? 0);
            $pr = (float) ($it['price'] ?? 0);
            $sum_bonded_invoices += ($qty + $qbf) * $pr;
         }
      }
      $x = 0.0;
      if ($sum_bonded_project > 0) {
         $x = $sum_bonded_invoices / (float) $sum_bonded_project;
      }
      $x = max(0.0, min(1.0, $x));
      $applied = min($x, $bon_quantity_available);
      $bon_amount = round($bon_general * $applied, 2);

      return [
         'items' => $items,
         'sum_bonded_project' => $sum_bonded_project,
         'bond_price' => $bond_price,
         'bon_general' => $bon_general,
         'bon_quantity_available' => $bon_quantity_available,
         'bon_quantity' => $applied,
         'bon_amount' => $bon_amount
      ];
   }

   /**
    * CalculaPaidAmountTotalFromPreviusInvoice
    * @param $project_item_id
    * @return float|int
    */
   public function CalculaPaidAmountTotalFromPreviusInvoice($project_item_id)
   {
      $total = 0;

      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
      $invoice_items = $invoiceItemRepo->ListarInvoicesDeItem($project_item_id);
      foreach ($invoice_items as $value) {
         $total += $value->getPaidAmount();
      }

      return $total;
   }

   /**
    * CalcularUnpaidQuantityFromPreviusInvoice
    * Suma los unpaid_qty de todos los invoices anteriores del mismo project_item
    * IMPORTANTE: NO usa el campo unpaid_qty almacenado en BD, sino que recorre
    * todos los invoices previos y aplica la fórmula: unpaid_qty = quantity_final - paid_qty
    * donde quantity_final = quantity + quantity_brought_forward
    * 
    * @param $project_item_id
    * @return float|int
    */
   /**
    * CalcularUnpaidQuantityFromPreviusInvoice
    * Suma los unpaid_qty de todos los invoices anteriores.
    * CORRECCIÓN: Se basa estrictamente en (Total Cantidad - Total Pagado).
    * Se IGNORA el quantity_brought_forward (QBF) para la deuda histórica,
    * ya que el QBF es un ajuste temporal de ese invoice y no debe aumentar la deuda futura.
    * * @param $project_item_id
    * @return float|int
    */
   public function CalcularUnpaidQuantityFromPreviusInvoice($project_item_id)
   {
      $total_quantity = 0.0;
      $total_paid = 0.0;

      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);

      // Obtener TODOS los invoice items anteriores de este project_item
      $invoice_items = $invoiceItemRepo->ListarInvoicesDeItem($project_item_id);

      // Recorrer TODOS los invoices previos
      foreach ($invoice_items as $invoice_item) {
         // Sumar solo la cantidad real facturada (Qty This Period)
         $quantity = $invoice_item->getQuantity();
         $quantity = ($quantity === null) ? 0.0 : (float)$quantity;

         // Sumar lo pagado
         $paid_quantity = $invoice_item->getPaidQty();
         $paid_quantity = ($paid_quantity === null) ? 0.0 : (float)$paid_quantity;

         // ACUMULAR
         // IMPORTANTE: No sumamos quantity_brought_forward aquí.
         $total_quantity += $quantity;
         $total_paid += $paid_quantity;
      }

      // La deuda total es simplemente lo facturado menos lo pagado
      $unpaid_quantity = $total_quantity - $total_paid;

      return max(0.0, $unpaid_quantity);
   }

   /**
    * CargarDatosProject: Carga los datos de un project
    *
    * @param int $project_id Id
    *
    * @author Marcel
    */
   public function CargarDatosProject($project_id)
   {
      $resultado = array();
      $arreglo_resultado = array();

      $entity = $this->getDoctrine()->getRepository(Project::class)
         ->find($project_id);
      /** @var Project $entity */
      if ($entity != null) {

         $arreglo_resultado['company_id'] = $entity->getCompany()->getCompanyId();
         $arreglo_resultado['company'] = $entity->getCompany()->getName();
         $arreglo_resultado['inspector_id'] = $entity->getInspector() != null ? $entity->getInspector()->getInspectorId() : '';
         $arreglo_resultado['inspector'] = $entity->getInspector() != null ? $entity->getInspector()->getName() : '';

         // Counties - obtener desde ProjectCountyRepository
         $projectCountyRepo = $this->getDoctrine()->getRepository(ProjectCounty::class);
         $projectCounties = $projectCountyRepo->ListarCountysDeProject($entity->getProjectId());
         $county_ids = [];
         $county_descriptions = [];
         foreach ($projectCounties as $projectCounty) {
            $county = $projectCounty->getCounty();
            if ($county !== null) {
               $county_ids[] = $county->getCountyId();
               $county_descriptions[] = $county->getDescription();
            }
         }
         $arreglo_resultado['county_id'] = $county_ids;
         $arreglo_resultado['county'] = implode(', ', $county_descriptions);
         // Mantener compatibilidad con código existente
         $arreglo_resultado['county_id_single'] = !empty($county_ids) ? $county_ids[0] : '';
         $arreglo_resultado['county_single'] = !empty($county_descriptions) ? $county_descriptions[0] : '';

         $arreglo_resultado['number'] = $entity->getProjectNumber();
         $arreglo_resultado['name'] = $entity->getName();
         $arreglo_resultado['description'] = $entity->getDescription();
         $arreglo_resultado['location'] = $entity->getLocation();
         $arreglo_resultado['po_number'] = $entity->getPoNumber();
         $arreglo_resultado['po_cg'] = $entity->getPoCG();
         $arreglo_resultado['manager'] = $entity->getManager();
         $arreglo_resultado['status'] = $entity->getStatus();
         $arreglo_resultado['owner'] = $entity->getOwner();
         $arreglo_resultado['subcontract'] = $entity->getSubcontract();
         $arreglo_resultado['federal_funding'] = $entity->getFederalFunding();
         $arreglo_resultado['resurfacing'] = $entity->getResurfacing();
         $arreglo_resultado['invoice_contact'] = $entity->getInvoiceContact();
         $arreglo_resultado['certified_payrolls'] = $entity->getCertifiedPayrolls();
         $arreglo_resultado['start_date'] = $entity->getStartDate() != '' ? $entity->getStartDate()->format('m/d/Y') : '';
         $arreglo_resultado['end_date'] = $entity->getEndDate() != '' ? $entity->getEndDate()->format('m/d/Y') : '';
         $arreglo_resultado['due_date'] = $entity->getDueDate() != '' ? $entity->getDueDate()->format('m/d/Y') : '';
         $arreglo_resultado['contract_amount'] = $entity->getContractAmount();
         $arreglo_resultado['proposal_number'] = $entity->getProposalNumber();
         $arreglo_resultado['project_id_number'] = $entity->getProjectIdNumber();

         $arreglo_resultado['vendor_id'] = $entity->getConcreteVendor() != null ? $entity->getConcreteVendor()->getVendorId() : '';
         $arreglo_resultado['concrete_class_id'] = $entity->getConcreteClass() != null ? $entity->getConcreteClass()->getConcreteClassId() : '';
         $arreglo_resultado['concrete_vendor'] = $entity->getConcreteVendor() != null ? $entity->getConcreteVendor()->getName() : '';
         $arreglo_resultado['concrete_quote_price'] = $entity->getConcreteQuotePrice() ?? '';
         $arreglo_resultado['concrete_quote_price_escalator'] = $entity->getConcreteQuotePriceEscalator() ?? '';
         $arreglo_resultado['concrete_time_period_every_n'] = $entity->getConcreteTimePeriodEveryN() ?? '';
         $arreglo_resultado['concrete_time_period_unit'] = $entity->getConcreteTimePeriodUnit() ?? '';

         $arreglo_resultado['retainage'] = $entity->getRetainage();
         $arreglo_resultado['retainage_percentage'] = $entity->getRetainagePercentage() ?? '';
         $arreglo_resultado['retainage_adjustment_percentage'] = $entity->getRetainageAdjustmentPercentage() ?? '';
         $arreglo_resultado['retainage_adjustment_completion'] = $entity->getRetainageAdjustmentCompletion() ?? '';

         $arreglo_resultado['prevailing_wage'] = $entity->getPrevailingWage();
         $arreglo_resultado['prevailing_county_id'] = $entity->getPrevailingCounty() != null ? $entity->getPrevailingCounty()->getCountyId() : '';
         $arreglo_resultado['prevailing_county'] = $entity->getPrevailingCounty() != null ? $entity->getPrevailingCounty()->getDescription() : '';
         $arreglo_resultado['prevailing_role_id'] = $entity->getPrevailingRole() != null ? $entity->getPrevailingRole()->getRoleId() : '';
         $arreglo_resultado['prevailing_role'] = $entity->getPrevailingRole() != null ? $entity->getPrevailingRole()->getDescription() : '';
         $arreglo_resultado['prevailing_rate'] = $entity->getPrevailingRate();
         // Bon General = monto del ítem Bond en el proyecto (calculado, no se guarda en project)
         $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
         $arreglo_resultado['bon_general'] = $projectItemRepo->TotalBondAmountProjectItems($project_id);

         // items
         $items = $this->ListarItemsDeProject($project_id);
         $arreglo_resultado['items'] = $items;

         // contacts
         $contacts = $this->ListarContactsDeProject($project_id);
         $arreglo_resultado['contacts'] = $contacts;

         // concrete classes
         $concrete_classes = $this->ListarConcreteClassesDeProject($project_id);
         $arreglo_resultado['concrete_classes'] = $concrete_classes;

         // ajustes precio
         $ajustes_precio = $this->ListarAjustesPrecioDeProject($project_id);
         $arreglo_resultado['ajustes_precio'] = $ajustes_precio;

         // invoices
         $invoices = $this->ListarInvoicesDeProject($project_id);
         $arreglo_resultado['invoices'] = $invoices;

         // archivos
         $archivos = $this->ListarArchivosDeProject($project_id);
         $arreglo_resultado['archivos'] = $archivos;

         // completion
         $items_completion = $this->ListarItemsCompletion($project_id);
         $arreglo_resultado['items_completion'] = $items_completion;

         // Invoices and Retainage History (paso 4 del tab Retainage)
         if ($entity->getRetainage()) {
            $invoices_retainage = $this->ListarInvoicesConRetainage($project_id);
            $arreglo_resultado['invoices_retainage'] = $invoices_retainage;
            $total_retainage_withheld = 0;
            if (!empty($invoices_retainage)) {
               $total_retainage_withheld = (float) ($invoices_retainage[0]['total_retainage_to_date'] ?? 0);
            }
            $arreglo_resultado['total_retainage_withheld'] = $total_retainage_withheld;
         }

         $resultado['success'] = true;
         $resultado['project'] = $arreglo_resultado;
      }

      return $resultado;
   }

   /**
    * ListarItemsCompletion
    * @param $project_id
    * @return array
    */
   public function ListarItemsCompletion($project_id, $fecha_inicial = "", $fecha_fin = "")
   {
      $items = [];

      /** @var ProjectItemRepository $projectItemRepo */
      $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
      $lista = $projectItemRepo->ListarItemsDeProject($project_id);
      foreach ($lista as $key => $value) {
         $project_item_id = $value->getId();
         $quantity = $value->getQuantity();
         $price = $value->getPrice();
         $total = $quantity * $price;

         /** @var DataTrackingItemRepository $dataTrackingItemRepo */
         $dataTrackingItemRepo = $this->getDoctrine()->getRepository(DataTrackingItem::class);
         $quantity_completed = $dataTrackingItemRepo->TotalQuantity("", $project_item_id, $fecha_inicial, $fecha_fin);

         $amount_completed = $quantity_completed * $price;

         // calcular porciento de completion
         $porciento_completion = $quantity > 0 ? $quantity_completed / $quantity * 100 : 0;

         // Calcular valores de invoices y payments
         /** @var InvoiceItemRepository $invoiceItemRepo */
         $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
         $invoiced_qty = $invoiceItemRepo->TotalInvoiceQuantityByProjectItem($project_item_id);
         $total_invoiced_amount = $invoiceItemRepo->TotalInvoiceAmountByProjectItem($project_item_id);
         $paid_qty = $invoiceItemRepo->TotalInvoicePaidQtyByProjectItem($project_item_id);
         $total_paid_amount = $invoiceItemRepo->TotalInvoicePaidAmountByProjectItem($project_item_id);

         // Verificar si hay historial de cantidad y precio
         /** @var ProjectItemHistoryRepository $historyRepo */
         $historyRepo = $this->getDoctrine()->getRepository(ProjectItemHistory::class);
         $has_quantity_history = $historyRepo->TieneHistorialCantidad($project_item_id);
         $has_price_history = $historyRepo->TieneHistorialPrecio($project_item_id);

         // Diff Qty = Paid qty - Inv qty; Diff Amt = Paid Amt - Inv Amt (igual que en el frontend admin)
         $invQty = (float) $invoiced_qty;
         $invAmt = (float) $total_invoiced_amount;
         $paidQty = (float) $paid_qty;
         $paidAmt = (float) $total_paid_amount;
         $diff_qty = $paidQty - $invQty;
         $diff_amt = $paidAmt - $invAmt;

         $items[] = [
            'project_item_id' => $project_item_id,
            "apply_retainage" => $value->getApplyRetainage(),
            "bonded" => $value->getBonded() ? 1 : 0,
            "bond" => $value->getItem()->getBond() ? 1 : 0,
            "item_id" => $value->getItem()->getItemId(),
            "item" => $value->getItem()->getName(),
            "unit" => $value->getItem()->getUnit() != null ? $value->getItem()->getUnit()->getDescription() : '',
            "quantity" => $quantity,
            "quantity_old" => $value->getQuantityOld() ?? '',
            "price" => $price,
            "price_old" => $value->getPriceOld() ?? '',
            "total" => $total,
            "quantity_completed" => $quantity_completed,
            "amount_completed" => $amount_completed,
            "porciento_completion" => $porciento_completion,
            "invoiced_qty" => $invoiced_qty,
            "total_invoiced_amount" => $total_invoiced_amount,
            "paid_qty" => $paid_qty,
            "total_paid_amount" => $total_paid_amount,
            "diff_qty" => $diff_qty,
            "diff_amt" => $diff_amt,
            "principal" => $value->getPrincipal(),
            "change_order" => $value->getChangeOrder(),
            "change_order_date" => $value->getChangeOrderDate() != null ? $value->getChangeOrderDate()->format('m/d/Y') : '',
            "has_quantity_history" => $has_quantity_history,
            "has_price_history" => $has_price_history,
            "posicion" => $key
         ];
      }

      return $items;
   }

   /**
    * ListarArchivosDeProject
    * @param $project_id
    * @return array
    */
   public function ListarArchivosDeProject($project_id)
   {
      $archivos = [];

      /** @var ProjectAttachmentRepository $projectAttachmentRepo */
      $projectAttachmentRepo = $this->getDoctrine()->getRepository(ProjectAttachment::class);
      $project_archivos = $projectAttachmentRepo->ListarAttachmentsDeProject($project_id);
      foreach ($project_archivos as $key => $project_archivo) {
         $archivos[] = [
            'id' => $project_archivo->getId(),
            'name' => $project_archivo->getName(),
            'file' => $project_archivo->getFile(),
            'posicion' => $key
         ];
      }

      return $archivos;
   }

   /**
    * ListarAjustesPrecioDeProject
    * @param $project_id
    * @return array
    */
   public function ListarAjustesPrecioDeProject($project_id)
   {
      $ajustes = [];

      /** @var ProjectPriceAdjustmentRepository $projectPriceAdjustmentRepo */
      $projectPriceAdjustmentRepo = $this->getDoctrine()->getRepository(ProjectPriceAdjustment::class);
      $project_ajustes = $projectPriceAdjustmentRepo->ListarAjustesDeProject($project_id);

      // Obtener todos los items del proyecto para construir los nombres
      /** @var ProjectItemRepository $projectItemRepo */
      $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
      $project_items = $projectItemRepo->ListarItemsDeProject($project_id);
      $items_map = [];
      foreach ($project_items as $project_item) {
         $item_id = $project_item->getItem()->getItemId();
         $item_name = $project_item->getItem()->getName();
         $unit = $project_item->getItem()->getUnit() != null ? $project_item->getItem()->getUnit()->getDescription() : '';
         $items_map[$item_id] = $item_name . ($unit ? ' - ' . $unit : '');
      }

      foreach ($project_ajustes as $key => $project_ajuste) {
         $items_id = $project_ajuste->getItemsId();
         $items_names = 'All items';

         if ($items_id && $items_id !== '') {
            $items_id_array = explode(',', $items_id);
            $items_names_array = [];
            foreach ($items_id_array as $item_id) {
               $item_id = trim($item_id);
               if (isset($items_map[$item_id])) {
                  $items_names_array[] = $items_map[$item_id];
               }
            }
            if (!empty($items_names_array)) {
               $items_names = implode(', ', $items_names_array);
            }
         }

         $ajustes[] = [
            'id' => $project_ajuste->getId(),
            'day' => $project_ajuste->getDay()->format('m/d/Y'),
            'percent' => $project_ajuste->getPercent(),
            'items_id' => $items_id ? $items_id : '',
            'items_names' => $items_names,
            'posicion' => $key
         ];
      }

      return $ajustes;
   }

   /**
    * ListarContactsDeProject
    * @param $project_id
    * @return array
    */
   public function ListarContactsDeProject($project_id)
   {
      $contacts = [];

      /** @var ProjectContactRepository $projectContactRepo */
      $projectContactRepo = $this->getDoctrine()->getRepository(ProjectContact::class);
      $project_contacts = $projectContactRepo->ListarContacts($project_id);

      foreach ($project_contacts as $key => $project_contact) {
         $companyContact = $project_contact->getCompanyContact();
         // Compatible with company_contact_id NULL (legacy): name/email/phone from stored fields
         $contacts[] = [
            'contact_id' => $project_contact->getContactId(),
            'company_contact_id' => $companyContact ? $companyContact->getContactId() : null,
            'name' => $project_contact->getName() ?? '',
            'email' => $project_contact->getEmail() ?? '',
            'phone' => $project_contact->getPhone() ?? '',
            'role' => $project_contact->getRole() ?? '',
            'notes' => $project_contact->getNotes() ?? '',
            'posicion' => $key
         ];
      }

      return $contacts;
   }

   /**
    * ListarConcreteClassesDeProject
    * @param $project_id
    * @return array
    */
   public function ListarConcreteClassesDeProject($project_id)
   {
      $concrete_classes = [];

      /** @var ProjectConcreteClassRepository $projectConcreteClassRepo */
      $projectConcreteClassRepo = $this->getDoctrine()->getRepository(ProjectConcreteClass::class);
      $project_concrete_classes = $projectConcreteClassRepo->ListarConcreteClassesDeProject($project_id);

      foreach ($project_concrete_classes as $key => $project_concrete_class) {
         $concrete_class = $project_concrete_class->getConcreteClass();
         $concrete_classes[] = [
            'id' => $project_concrete_class->getId(),
            'concrete_class_id' => $concrete_class ? $concrete_class->getConcreteClassId() : '',
            'concrete_class_name' => $concrete_class ? $concrete_class->getName() : '',
            'concrete_quote_price' => $project_concrete_class->getConcreteQuotePrice(),
            'posicion' => $key
         ];
      }

      return $concrete_classes;
   }

   /**
    * ListarInvoicesDeProject
    * @param $project_id
    * @return array
    */
   public function ListarInvoicesDeProject($project_id)
   {
      $invoices = [];

      /** @var InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
      $lista = $invoiceRepo->ListarInvoicesDeProject($project_id);
      foreach ($lista as $key => $value) {

         $invoice_id = $value->getInvoiceId();

         /** @var InvoiceItemRepository $invoiceItemRepo */
         $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
         // Usar TotalInvoiceFinalAmountThisPeriod para calcular el total (suma de Final Amount This Period)
         $total = $invoiceItemRepo->TotalInvoiceFinalAmountThisPeriod((string) $invoice_id);

         $invoice = [
            "invoice_id" => $invoice_id,
            "number" => $value->getNumber(),
            "company" => $value->getProject()->getCompany()->getName(),
            "project" => $value->getProject()->getName(),
            "startDate" => $value->getStartDate()->format('m/d/Y'),
            "endDate" => $value->getEndDate()->format('m/d/Y'),
            "notes" => $this->truncate($value->getNotes(), 50),
            "total" => number_format($total, 2, '.', ','),
            "createdAt" => $value->getCreatedAt()->format('m/d/Y'),
            "paid" => $value->getPaid() ? 1 : 0,
            "posicion" => $key
         ];
         $invoices[] = $invoice;
      }

      return $invoices;
   }

   /**
    * ListarNotesDeProject: Lista las notas del proyecto para la app (tab Notes).
    * @param string|int $project_id
    * @return array[] id, date (m/d/Y), notes
    */
   public function ListarNotesDeProject($project_id)
   {
      $data = [];
      /** @var ProjectNotesRepository $projectNotesRepo */
      $projectNotesRepo = $this->getDoctrine()->getRepository(ProjectNotes::class);
      $lista = $projectNotesRepo->ListarNotesDeProject($project_id, '', '', 'DESC');
      foreach ($lista as $value) {
         $date = $value->getDate();
         $data[] = [
            'id' => $value->getId(),
            'date' => $date !== null ? $date->format('m/d/Y') : '',
            'notes' => $value->getNotes() ?? '',
         ];
      }
      return $data;
   }

   /**
    * ListarItemsDeProject
    * @param $project_id
    * @return array
    */
   public function ListarItemsDeProject($project_id)
   {
      $items = [];

      /** @var ProjectItemRepository $projectItemRepo */
      $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
      $lista = $projectItemRepo->ListarItemsDeProject($project_id);
      foreach ($lista as $key => $value) {
         $item = $this->DevolverItemDeProject($value, $key);
         $items[] = $item;
      }

      return $items;
   }

   /**
    * DevolverItemDeProject
    * @param ProjectItem $value
    * @return array
    */
   public function DevolverItemDeProject($value, $key = -1)
   {
      $yield_calculation_name = $this->DevolverYieldCalculationDeItemProject($value);

      $quantity = $value->getQuantity();
      $price = $value->getPrice();
      $total = $quantity * $price;

      // Calcular porcentaje de completion
      $project_item_id = $value->getId();
      /** @var DataTrackingItemRepository $dataTrackingItemRepo */
      $dataTrackingItemRepo = $this->getDoctrine()->getRepository(DataTrackingItem::class);
      $quantity_completed = $dataTrackingItemRepo->TotalQuantity("", $project_item_id, "", "");
      $porciento_completion = $quantity > 0 ? $quantity_completed / $quantity * 100 : 0;

      // Calcular valores de invoices y payments
      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
      $invoiced_qty = $invoiceItemRepo->TotalInvoiceQuantityByProjectItem($project_item_id);
      $total_invoiced_amount = $invoiceItemRepo->TotalInvoiceAmountByProjectItem($project_item_id);
      $paid_qty = $invoiceItemRepo->TotalInvoicePaidQtyByProjectItem($project_item_id);
      $total_paid_amount = $invoiceItemRepo->TotalInvoicePaidAmountByProjectItem($project_item_id);

      // Verificar si hay historial de cantidad y precio
      /** @var ProjectItemHistoryRepository $historyRepo */
      $historyRepo = $this->getDoctrine()->getRepository(ProjectItemHistory::class);
      $has_quantity_history = $historyRepo->TieneHistorialCantidad($project_item_id);
      $has_price_history = $historyRepo->TieneHistorialPrecio($project_item_id);

      return [
         'id' => $value->getId(),
         'apply_retainage' => $value->getApplyRetainage(),
         'bonded' => $value->getBonded() ? 1 : 0,
         // ---------------------------------
         'project_item_id' => $value->getId(),
         "item_id" => $value->getItem()->getItemId(),
         "item" => $value->getItem()->getName(),
         "unit" => $value->getItem()->getUnit() != null ? $value->getItem()->getUnit()->getDescription() : '',
         "quantity" => $quantity,
         "quantity_old" => $value->getQuantityOld() ?? '',
         "price" => $price,
         "price_old" => $value->getPriceOld() ?? '',
         "total" => $total,
         "yield_calculation" => $value->getYieldCalculation(),
         "yield_calculation_name" => $yield_calculation_name,
         "equation_id" => $value->getEquation() != null ? $value->getEquation()->getEquationId() : '',
         "principal" => $value->getPrincipal(),
         "change_order" => $value->getChangeOrder(),
         "change_order_date" => $value->getChangeOrderDate() != null ? $value->getChangeOrderDate()->format('m/d/Y') : '',
         "bond" => $value->getItem()->getBond() ? 1 : 0,
         "porciento_completion" => $porciento_completion,
         "invoiced_qty" => $invoiced_qty,
         "total_invoiced_amount" => $total_invoiced_amount,
         "paid_qty" => $paid_qty,
         "total_paid_amount" => $total_paid_amount,
         "has_quantity_history" => $has_quantity_history,
         "has_price_history" => $has_price_history,
         "posicion" => $key
      ];
   }

   /**
    * ObtenerPorcentajeCompletionItem: Obtiene el porcentaje de completion de un item específico
    * @param int $project_item_id Id del project item
    * @return float
    * @author Marcel
    */
   public function ObtenerPorcentajeCompletionItem($project_item_id)
   {
      /** @var ProjectItemRepository $projectItemRepo */
      $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
      $projectItem = $projectItemRepo->find($project_item_id);

      if (!$projectItem) {
         return 0;
      }

      $quantity = $projectItem->getQuantity();

      if ($quantity <= 0) {
         return 0;
      }

      /** @var DataTrackingItemRepository $dataTrackingItemRepo */
      $dataTrackingItemRepo = $this->getDoctrine()->getRepository(DataTrackingItem::class);
      $quantity_completed = $dataTrackingItemRepo->TotalQuantity("", $project_item_id, "", "");

      $porciento_completion = $quantity_completed / $quantity * 100;

      return $porciento_completion;
   }

   /**
    * EliminarProject: Elimina un rol en la BD
    * @param int $project_id Id
    * @author Marcel
    */
   public function EliminarProject($project_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Project::class)
         ->find($project_id);
      /**@var Project $entity */
      if ($entity != null) {

         // eliminar informacion de un project
         $this->EliminarInformacionDeProject($project_id);

         $project_descripcion = $entity->getName();


         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Project";
         $log_descripcion = "The project is deleted: $project_descripcion";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   private function EliminarInformacionDeProject($project_id)
   {
      $em = $this->getDoctrine()->getManager();

      // counties
      /** @var ProjectCountyRepository $projectCountyRepo */
      $projectCountyRepo = $this->getDoctrine()->getRepository(ProjectCounty::class);
      $projectCounties = $projectCountyRepo->ListarCountysDeProject($project_id);
      foreach ($projectCounties as $projectCounty) {
         $em->remove($projectCounty);
      }

      // schedules
      /** @var ScheduleRepository $scheduleRepo */
      $scheduleRepo = $this->getDoctrine()->getRepository(Schedule::class);
      $schedules = $scheduleRepo->ListarSchedulesDeProject($project_id);
      foreach ($schedules as $schedule) {
         $schedule_id = $schedule->getScheduleId();

         // contacts
         /** @var ScheduleConcreteVendorContactRepository $scheduleConcreteVendorContactRepo */
         $scheduleConcreteVendorContactRepo = $this->getDoctrine()->getRepository(ScheduleConcreteVendorContact::class);
         $schedules_contact = $scheduleConcreteVendorContactRepo->ListarContactosDeSchedule($schedule_id);
         foreach ($schedules_contact as $schedule_contact) {
            $em->remove($schedule_contact);
         }

         // employees
         /** @var ScheduleEmployeeRepository $scheduleEmployeeRepo */
         $scheduleEmployeeRepo = $this->getDoctrine()->getRepository(ScheduleEmployee::class);
         $schedules_employees = $scheduleEmployeeRepo->ListarEmployeesDeSchedule($schedule_id);
         foreach ($schedules_employees as $schedules_employee) {
            $em->remove($schedules_employee);
         }

         $em->remove($schedule);
      }


      // invoices
      /** @var InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
      $invoices = $invoiceRepo->ListarInvoicesDeProject($project_id);
      foreach ($invoices as $invoice) {
         $invoice_id = $invoice->getInvoiceId();

         // eliminar informacion
         $this->EliminarInformacionDeInvoice($invoice_id);

         $em->remove($invoice);
      }

      // contacts
      /** @var ProjectContactRepository $projectContactRepo */
      $projectContactRepo = $this->getDoctrine()->getRepository(ProjectContact::class);
      $contacts = $projectContactRepo->ListarContacts($project_id);
      foreach ($contacts as $contact) {
         $em->remove($contact);
      }

      // concrete classes
      /** @var ProjectConcreteClassRepository $projectConcreteClassRepo */
      $projectConcreteClassRepo = $this->getDoctrine()->getRepository(ProjectConcreteClass::class);
      $concrete_classes = $projectConcreteClassRepo->ListarConcreteClassesDeProject($project_id);
      foreach ($concrete_classes as $concrete_class) {
         $em->remove($concrete_class);
      }

      // items
      /** @var ProjectItemRepository $projectItemRepo */
      $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
      $items = $projectItemRepo->ListarItemsDeProject($project_id);
      foreach ($items as $item) {

         // subcontractors
         /** @var DataTrackingSubcontractRepository $dataTrackingSubcontractRepo */
         $dataTrackingSubcontractRepo = $this->getDoctrine()->getRepository(DataTrackingSubcontract::class);
         $data_tracking_subcontractors = $dataTrackingSubcontractRepo->ListarSubcontractsDeItemProject($item->getId());
         foreach ($data_tracking_subcontractors as $subcontractor) {
            $em->remove($subcontractor);
         }

         $em->remove($item);
      }

      // data tracking
      /** @var DataTrackingRepository $dataTrackingRepo */
      $dataTrackingRepo = $this->getDoctrine()->getRepository(DataTracking::class);
      $data_tracking = $dataTrackingRepo->ListarDataTracking($project_id);
      foreach ($data_tracking as $data) {

         // eliminar informacion data tracking
         $this->EliminarInformacionRelacionadaDataTracking($data->getId());

         $em->remove($data);
      }

      // notes
      /** @var ProjectNotesRepository $projectNotesRepo */
      $projectNotesRepo = $this->getDoctrine()->getRepository(ProjectNotes::class);
      $notes = $projectNotesRepo->ListarNotesDeProject($project_id);
      foreach ($notes as $note) {
         $em->remove($note);
      }

      // notificaciones
      /** @var NotificationRepository $notificationRepo */
      $notificationRepo = $this->getDoctrine()->getRepository(Notification::class);
      $notificaciones = $notificationRepo->ListarNotificacionesDeProject($project_id);
      foreach ($notificaciones as $notificacion) {
         $em->remove($notificacion);
      }

      // prices adjuments
      /** @var ProjectPriceAdjustmentRepository $projectPriceAdjustmentRepo */
      $projectPriceAdjustmentRepo = $this->getDoctrine()->getRepository(ProjectPriceAdjustment::class);
      $ajustes_precio = $projectPriceAdjustmentRepo->ListarAjustesDeProject($project_id);
      foreach ($ajustes_precio as $ajuste_precio) {
         $em->remove($ajuste_precio);
      }

      // attachments
      $dir = 'uploads/project/';
      /** @var ProjectAttachmentRepository $projectAttachmentRepo */
      $projectAttachmentRepo = $this->getDoctrine()->getRepository(ProjectAttachment::class);
      $attachments = $projectAttachmentRepo->ListarAttachmentsDeProject($project_id);
      foreach ($attachments as $attachment) {

         //eliminar archivo
         $file_eliminar = $attachment->getFile();
         if ($file_eliminar != "" && is_file($dir . $file_eliminar)) {
            unlink($dir . $file_eliminar);
         }

         $em->remove($attachment);
      }
   }

   /**
    * EliminarProjects: Elimina los projects seleccionados en la BD
    * @param int $ids Ids
    * @author Marcel
    */
   public function EliminarProjects($ids)
   {
      $em = $this->getDoctrine()->getManager();

      if ($ids != "") {
         $ids = explode(',', $ids);
         $cant_eliminada = 0;
         $cant_total = 0;
         foreach ($ids as $project_id) {
            if ($project_id != "") {
               $cant_total++;
               $entity = $this->getDoctrine()->getRepository(Project::class)
                  ->find($project_id);
               /**@var Project $entity */
               if ($entity != null) {

                  // eliminar informacion de un project
                  $this->EliminarInformacionDeProject($project_id);

                  $project_descripcion = $entity->getName();

                  $em->remove($entity);
                  $cant_eliminada++;

                  //Salvar log
                  $log_operacion = "Delete";
                  $log_categoria = "Project";
                  $log_descripcion = "The project is deleted: $project_descripcion";
                  $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
               }
            }
         }
      }
      $em->flush();

      if ($cant_eliminada == 0) {
         $resultado['success'] = false;
         $resultado['error'] = "The projects could not be deleted, because they are associated with a invoice";
      } else {
         $resultado['success'] = true;

         $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected projects because they are associated with a invoice";
         $resultado['message'] = $mensaje;
      }

      return $resultado;
   }

   /**
    * ActualizarProject: Actuializa los datos del rol en la BD
    * @param int $project_id Id
    * @author Marcel
    */
   public function ActualizarProject(
      $project_id,
      $company_id,
      $inspector_id,
      $number,
      $name,
      $description,
      $location,
      $po_number,
      $po_cg,
      $manager,
      $status,
      $owner,
      $subcontract,
      $federal_funding,
      $county_ids,
      $resurfacing,
      $invoice_contact,
      $certified_payrolls,
      $start_date,
      $end_date,
      $due_date,
      $contract_amount,
      $proposal_number,
      $project_id_number,
      $items,
      $contacts,
      $concrete_classes,
      $ajustes_precio,
      $archivos,
      $vendor_id,
      $concrete_class_id,
      $concrete_quote_price,
      $concrete_quote_price_escalator,
      $concrete_time_period_every_n,
      $concrete_time_period_unit,
      $retainage,
      $retainage_percentage,
      $retainage_adjustment_percentage,
      $retainage_adjustment_completion,
      $prevailing_wage,
      $prevailing_county_id,
      $prevailing_role_id,
      $prevailing_rate
   ) {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Project::class)
         ->find($project_id);
      /** @var Project $entity */
      if ($entity != null) {
         //Verificar description
         $project = $this->getDoctrine()->getRepository(Project::class)
            ->findOneBy(['projectNumber' => $number]);
         if ($project != null && $entity->getProjectId() != $project->getProjectId()) {
            $resultado['success'] = false;
            $resultado['error'] = "The project number is in use, please try entering another one.";
            return $resultado;
         }

         // para guardar los cambios
         $notas = [];

         if ($number != $entity->getProjectNumber()) {
            $notas[] = [
               'notes' => 'Change project number, old value: ' . $entity->getProjectNumber(),
               'date' => new \DateTime()
            ];
         }

         $entity->setProjectNumber($number);


         if ($name != $entity->getName()) {
            $notas[] = [
               'notes' => 'Change name, old value: ' . $entity->getName(),
               'date' => new \DateTime()
            ];
         }
         $entity->setName($name);

         if ($description != $entity->getDescription()) {
            $notas[] = [
               'notes' => 'Change description, old value: ' . $entity->getDescription(),
               'date' => new \DateTime()
            ];
         }
         $entity->setDescription($description);

         if ($location != $entity->getLocation()) {
            $notas[] = [
               'notes' => 'Change location, old value: ' . $entity->getLocation(),
               'date' => new \DateTime()
            ];
         }
         $entity->setLocation($location);

         $entity->setPoNumber($po_number);
         $entity->setPoCG($po_cg);

         if ($manager != $entity->getManager()) {
            $notas[] = [
               'notes' => 'Change manager, old value: ' . $entity->getManager(),
               'date' => new \DateTime()
            ];
         }
         $entity->setManager($manager);

         if ($status != $entity->getStatus()) {
            // definir el valor del status
            switch ($entity->getStatus()) {
               case 0:
                  $old_status = "Not Started";
                  break;
               case 1:
                  $old_status = "In Progress";
                  break;
               default:
                  $old_status = "Completed";
                  break;
            }

            $notas[] = [
               'notes' => 'Change status, old value: ' . $old_status,
               'date' => new \DateTime()
            ];
         }
         $entity->setStatus($status);

         if ($contract_amount != $entity->getContractAmount()) {
            $notas[] = [
               'notes' => 'Change contract amount, old value: ' . $entity->getContractAmount(),
               'date' => new \DateTime()
            ];
         }
         $entity->setContractAmount($contract_amount);

         if ($proposal_number != $entity->getProposalNumber()) {
            $notas[] = [
               'notes' => 'Change proposal id #, old value: ' . $entity->getProposalNumber(),
               'date' => new \DateTime()
            ];
         }
         $entity->setProposalNumber($proposal_number);


         if ($project_id_number != $entity->getProjectIdNumber()) {
            $notas[] = [
               'notes' => 'Change project id #, old value: ' . $entity->getProjectIdNumber(),
               'date' => new \DateTime()
            ];
         }
         $entity->setProjectIdNumber($project_id_number);


         if ($company_id != '') {

            if ($entity->getCompany() && $company_id != $entity->getCompany()->getCompanyId()) {
               $notas[] = [
                  'notes' => 'Change company, old value: ' . $entity->getCompany()->getName(),
                  'date' => new \DateTime()
               ];
            }

            $company = $this->getDoctrine()->getRepository(Company::class)
               ->find($company_id);
            $entity->setCompany($company);
         }


         if ($inspector_id != '') {

            if ($entity->getInspector() && $inspector_id != $entity->getInspector()->getInspectorId()) {
               $notas[] = [
                  'notes' => 'Change inspector, old value: ' . $entity->getInspector()->getName(),
                  'date' => new \DateTime()
               ];
            }

            $inspector = $this->getDoctrine()->getRepository(Inspector::class)
               ->find($inspector_id);
            $entity->setInspector($inspector);
         }



         if ($owner != $entity->getOwner()) {
            $notas[] = [
               'notes' => 'Change owner, old value: ' . $entity->getOwner(),
               'date' => new \DateTime()
            ];
         }
         $entity->setOwner($owner);

         if ($subcontract != $entity->getSubcontract()) {
            $notas[] = [
               'notes' => 'Change Subcontract NO, old value: ' . $entity->getSubcontract(),
               'date' => new \DateTime()
            ];
         }
         $entity->setSubcontract($subcontract);

         if ($federal_funding != $entity->getFederalFunding()) {
            $notas[] = [
               'notes' => 'Change federal funding, old value: ' . $entity->getFederalFunding() ? 'Yes' : 'No',
               'date' => new \DateTime()
            ];
         }
         $entity->setFederalFunding($federal_funding);

         if ($resurfacing != $entity->getResurfacing()) {
            $notas[] = [
               'notes' => 'Change resurfacing, old value: ' . $entity->getResurfacing() ? 'Yes' : 'No',
               'date' => new \DateTime()
            ];
         }
         $entity->setResurfacing($resurfacing);

         if ($invoice_contact != $entity->getInvoiceContact()) {
            $notas[] = [
               'notes' => 'Change invoice contact, old value: ' . $entity->getInvoiceContact(),
               'date' => new \DateTime()
            ];
         }
         $entity->setInvoiceContact($invoice_contact);

         if ($certified_payrolls != $entity->getCertifiedPayrolls()) {
            $notas[] = [
               'notes' => 'Change certified payrolls, old value: ' . $entity->getCertifiedPayrolls() ? 'Yes' : 'No',
               'date' => new \DateTime()
            ];
         }
         $entity->setCertifiedPayrolls($certified_payrolls);


         // start date
         $start_date_old = $entity->getStartDate() != '' ? $entity->getStartDate()->format('m/d/Y') : '';
         if ($start_date != '') {

            if ($start_date != $start_date_old) {
               $notas[] = [
                  'notes' => 'Change start date, old value: ' . preg_replace('/\/00(\d{2})$/', '/20$1', $start_date_old),
                  'date' => new \DateTime()
               ];
            }


            $start_date = \DateTime::createFromFormat('m/d/Y', $start_date);
            $entity->setStartDate($start_date);
         }


         // end date
         $end_date_old = $entity->getEndDate() != '' ? $entity->getEndDate()->format('m/d/Y') : '';
         if ($end_date != '') {

            if ($end_date != $end_date_old) {
               $notas[] = [
                  'notes' => 'Change end date, old value: ' . preg_replace('/\/00(\d{2})$/', '/20$1', $end_date_old),
                  'date' => new \DateTime()
               ];
            }

            $end_date = \DateTime::createFromFormat('m/d/Y', $end_date);
            $entity->setEndDate($end_date);
         }

         // due date
         $due_date_old = $entity->getDueDate() != '' ? $entity->getDueDate()->format('m/d/Y') : '';
         $entity->setDueDate(NULL);
         if ($due_date != '') {
            if ($due_date != $due_date_old) {
               $notas[] = [
                  'notes' => 'Change due date, old value: ' . preg_replace('/\/00(\d{2})$/', '/20$1', $due_date_old),
                  'date' => new \DateTime()
               ];
            }

            $due_date = \DateTime::createFromFormat('m/d/Y', $due_date);
            $entity->setDueDate($due_date);
         }


         // conc vendor
         $vendor_id_old = $entity->getConcreteVendor() ? $entity->getConcreteVendor()->getVendorId() : "";
         $vendor_descripcion_old = $entity->getConcreteVendor() ? $entity->getConcreteVendor()->getName() : "";
         $entity->setConcreteVendor(NULL);
         if ($vendor_id != '') {
            $vendor = $this->getDoctrine()->getRepository(ConcreteVendor::class)
               ->find($vendor_id);
            $entity->setConcreteVendor($vendor);
         }

         // concrete class
         $entity->setConcreteClass(NULL);
         if ($concrete_class_id != '') {
            $concrete_class = $this->getDoctrine()->getRepository(ConcreteClass::class)
               ->find($concrete_class_id);
            $entity->setConcreteClass($concrete_class);
         }

         if ($vendor_id != $vendor_id_old) {
            $notas[] = [
               'notes' => 'Change concrete vendor, old value: ' . $vendor_descripcion_old,
               'date' => new \DateTime()
            ];
         }

         if ($concrete_quote_price != $entity->getConcreteQuotePrice()) {
            $notas[] = [
               'notes' => 'Change concrete quote price, old value: ' . $entity->getConcreteQuotePrice(),
               'date' => new \DateTime()
            ];

            $entity->setUpdatedAtConcreteQuotePrice(new \DateTime());
         }
         $valQuotePrice = ($concrete_quote_price !== '' && $concrete_quote_price !== null) ? (float)$concrete_quote_price : null;
         $entity->setConcreteQuotePrice($valQuotePrice);

         $valEscalator = ($concrete_quote_price_escalator !== '' && $concrete_quote_price_escalator !== null) ? (float)$concrete_quote_price_escalator : null;
         if ($valEscalator != $entity->getConcreteQuotePriceEscalator()) {
            $notas[] = [
               'notes' => 'Change concrete quote price escalator, old value: ' . $entity->getConcreteQuotePriceEscalator(),
               'date' => new \DateTime()
            ];
         }
         $entity->setConcreteQuotePriceEscalator($valEscalator);

         $valTpEveryN = ($concrete_time_period_every_n !== '' && $concrete_time_period_every_n !== null) ? (int)$concrete_time_period_every_n : null;
         if ($valTpEveryN != $entity->getConcreteTimePeriodEveryN()) {
            $notas[] = [
               'notes' => 'Change concrete time periodo every n, old value: ' . $entity->getConcreteTimePeriodEveryN(),
               'date' => new \DateTime()
            ];
         }
         $entity->setConcreteTimePeriodEveryN($valTpEveryN);

         if ($concrete_time_period_unit != $entity->getConcreteTimePeriodUnit()) {
            $notas[] = [
               'notes' => 'Change concrete time periodo unit, old value: ' . $entity->getConcreteTimePeriodUnit(),
               'date' => new \DateTime()
            ];
         }
         $entity->setConcreteTimePeriodUnit($concrete_time_period_unit);

         if ($retainage != $entity->getRetainage()) {
            $notas[] = [
               'notes' => 'Change retainage, old value: ' . $entity->getRetainage() ? 'Yes' : 'No',
               'date' => new \DateTime()
            ];
         }
         $entity->setRetainage($retainage);

         $valRetPct = ($retainage_percentage !== '' && $retainage_percentage !== null) ? (float)$retainage_percentage : null;
         if ($valRetPct != $entity->getRetainagePercentage()) {
            $notas[] = [
               'notes' => 'Change retainage percentage, old value: ' . $entity->getRetainagePercentage(),
               'date' => new \DateTime()
            ];
         }
         $entity->setRetainagePercentage($valRetPct);

         $valRetAdjPct = ($retainage_adjustment_percentage !== '' && $retainage_adjustment_percentage !== null) ? (float)$retainage_adjustment_percentage : null;
         if ($valRetAdjPct != $entity->getRetainageAdjustmentPercentage()) {
            $notas[] = [
               'notes' => 'Change retainage adjustment percentage, old value: ' . $entity->getRetainageAdjustmentPercentage(),
               'date' => new \DateTime()
            ];
         }
         $entity->setRetainageAdjustmentPercentage($valRetAdjPct);

         $valRetAdjComp = ($retainage_adjustment_completion !== '' && $retainage_adjustment_completion !== null) ? (float)$retainage_adjustment_completion : null;
         if ($valRetAdjComp != $entity->getRetainageAdjustmentCompletion()) {
            $notas[] = [
               'notes' => 'Change retainage adjustment completion, old value: ' . $entity->getRetainageAdjustmentCompletion(),
               'date' => new \DateTime()
            ];
         }
         $entity->setRetainageAdjustmentCompletion($valRetAdjComp);

         if ($prevailing_wage != $entity->getPrevailingWage()) {
            $notas[] = [
               'notes' => 'Change prevailing wage, old value: ' . $entity->getPrevailingWage() ? 'Yes' : 'No',
               'date' => new \DateTime()
            ];
         }
         $entity->setPrevailingWage($prevailing_wage);

         $prevailingCountyOld = $entity->getPrevailingCounty();
         $prevailingCountyOldId = $prevailingCountyOld != null ? $prevailingCountyOld->getCountyId() : null;
         if ($prevailing_county_id != $prevailingCountyOldId) {
            $oldValue = $prevailingCountyOld != null ? $prevailingCountyOld->getDescription() : 'None';
            $notas[] = [
               'notes' => 'Change prevailing county, old value: ' . $oldValue,
               'date' => new \DateTime()
            ];
         }

         // Buscar y establecer el objeto County
         if ($prevailing_county_id != '') {
            $prevailing_county = $this->getDoctrine()->getRepository(County::class)
               ->find($prevailing_county_id);
            $entity->setPrevailingCounty($prevailing_county);
         } else {
            $entity->setPrevailingCounty(null);
         }

         $prevailingRoleOld = $entity->getPrevailingRole();
         $prevailingRoleOldId = $prevailingRoleOld != null ? $prevailingRoleOld->getRoleId() : null;
         if ($prevailing_role_id != $prevailingRoleOldId) {
            $oldValue = $prevailingRoleOld != null ? $prevailingRoleOld->getDescription() : 'None';
            $notas[] = [
               'notes' => 'Change prevailing role, old value: ' . $oldValue,
               'date' => new \DateTime()
            ];
         }

         // Buscar y establecer el objeto EmployeeRole
         if ($prevailing_role_id != '') {
            $prevailing_role = $this->getDoctrine()->getRepository(EmployeeRole::class)
               ->find($prevailing_role_id);
            $entity->setPrevailingRole($prevailing_role);
         } else {
            $entity->setPrevailingRole(null);
         }

         $valPrevRate = ($prevailing_rate !== '' && $prevailing_rate !== null) ? (float)$prevailing_rate : null;
         if ($valPrevRate != $entity->getPrevailingRate()) {
            $notas[] = [
               'notes' => 'Change prevailing rate, old value: ' . $entity->getPrevailingRate(),
               'date' => new \DateTime()
            ];
         }
         $entity->setPrevailingRate($valPrevRate);

         $entity->setUpdatedAt(new \DateTime());

         // counties
         $county_changes = $this->SalvarCounties($entity, $county_ids, true);
         if ($county_changes['changed']) {
            $notas[] = [
               'notes' => 'Change counties, old values: ' . $county_changes['old_descriptions'],
               'date' => new \DateTime()
            ];
         }
         // items
         $items_new = $this->SalvarItems($entity, $items);
         // save contacts
         $this->SalvarContacts($entity, $contacts);
         // save concrete classes
         $this->SalvarConcreteClasses($entity, $concrete_classes);
         // save ajustes de precio
         $this->SalvarAjustesPrecio($entity, $ajustes_precio);
         // save archivos
         $this->SalvarArchivos($entity, $archivos);

         // save notes
         $this->SalvarNotesUpdate($entity, $notas);

         $em->flush();

         //Salvar log
         $log_operacion = "Update";
         $log_categoria = "Project";
         $log_descripcion = "The project is modified: $name";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
         $resultado['project_id'] = $project_id;
         $resultado['items'] = $items_new;

         return $resultado;
      }
   }

   /**
    * SalvarProject: Guarda los datos de project en la BD
    * @param string $description Nombre
    * @author Marcel
    */
   public function SalvarProject(
      $company_id,
      $inspector_id,
      $number,
      $name,
      $description,
      $location,
      $po_number,
      $po_cg,
      $manager,
      $status,
      $owner,
      $subcontract,
      $federal_funding,
      $county_ids,
      $resurfacing,
      $invoice_contact,
      $certified_payrolls,
      $start_date,
      $end_date,
      $due_date,
      $contract_amount,
      $proposal_number,
      $project_id_number,
      $items,
      $contacts,
      $concrete_classes,
      $vendor_id,
      $concrete_class_id,
      $concrete_quote_price,
      $concrete_quote_price_escalator,
      $concrete_time_period_every_n,
      $concrete_time_period_unit,
      $retainage,
      $retainage_percentage,
      $retainage_adjustment_percentage,
      $retainage_adjustment_completion,
      $prevailing_wage,
      $prevailing_county_id,
      $prevailing_role_id,
      $prevailing_rate
   ) {
      $em = $this->getDoctrine()->getManager();

      //Verificar number
      $project = $this->getDoctrine()->getRepository(Project::class)
         ->findOneBy(['projectNumber' => $number]);
      if ($project != null) {
         $resultado['success'] = false;
         $resultado['error'] = "The project number is in use, please try entering another one.";
         return $resultado;
      }

      $entity = new Project();

      $entity->setProjectNumber($number);
      $entity->setName($name);
      $entity->setDescription($description);
      $entity->setLocation($location);
      $entity->setPoNumber($po_number);
      $entity->setPoCG($po_cg);
      $entity->setManager($manager);
      $entity->setStatus($status);
      $contract_amount = str_replace(['$', ','], '', $contract_amount);
      $entity->setContractAmount($contract_amount);
      $entity->setProposalNumber($proposal_number);
      $entity->setProjectIdNumber($project_id_number);

      if ($company_id != '') {
         $company = $this->getDoctrine()->getRepository(Company::class)
            ->find($company_id);
         $entity->setCompany($company);
      }
      if ($inspector_id != '') {
         $inspector = $this->getDoctrine()->getRepository(Inspector::class)
            ->find($inspector_id);
         $entity->setInspector($inspector);
      }

      $entity->setOwner($owner);
      $entity->setSubcontract($subcontract);
      $entity->setFederalFunding($federal_funding);
      $entity->setResurfacing($resurfacing);
      $entity->setInvoiceContact($invoice_contact);
      $entity->setCertifiedPayrolls($certified_payrolls);

      if ($start_date != '') {
         $start_date = \DateTime::createFromFormat('m/d/Y', $start_date);
         $entity->setStartDate($start_date);
      }

      if ($end_date != '') {
         $end_date = \DateTime::createFromFormat('m/d/Y', $end_date);
         $entity->setEndDate($end_date);
      }

      if ($due_date != '') {
         $due_date = \DateTime::createFromFormat('m/d/Y', $due_date);
         $entity->setDueDate($due_date);
      }

      if ($vendor_id !== "") {
         $conc_vendor = $this->getDoctrine()->getRepository(ConcreteVendor::class)
            ->find($vendor_id);
         $entity->setConcreteVendor($conc_vendor);
      }


      if ($concrete_class_id != '') {

         $id_class = $concrete_class_id;

         if (is_object($id_class)) {
            $id_class = isset($id_class->concreteClassId) ? $id_class->concreteClassId : (isset($id_class->id) ? $id_class->id : null);
         } elseif (is_array($id_class)) {

            $id_class = isset($id_class['concreteClassId']) ? $id_class['concreteClassId'] : (isset($id_class['id']) ? $id_class['id'] : null);
         }

         if ($id_class) {
            $concrete_class = $this->getDoctrine()->getRepository(ConcreteClass::class)
               ->find($id_class);


            $entity->setConcreteClass($concrete_class);
         }
      }

      // $entity->setConcreteQuotePrice($concrete_quote_price);
      $val = ($concrete_quote_price !== '' && $concrete_quote_price !== null) ? (float)$concrete_quote_price : null;
      $entity->setConcreteQuotePrice($val);
      $valEscalator = ($concrete_quote_price_escalator !== '' && $concrete_quote_price_escalator !== null) ? (float)$concrete_quote_price_escalator : null;
      $entity->setConcreteQuotePriceEscalator($valEscalator);
      $valTpEveryN = ($concrete_time_period_every_n !== '' && $concrete_time_period_every_n !== null) ? (int)$concrete_time_period_every_n : null;
      $entity->setConcreteTimePeriodEveryN($valTpEveryN);
      $entity->setConcreteTimePeriodUnit($concrete_time_period_unit);

      if ($concrete_quote_price !== '') {
         $entity->setUpdatedAtConcreteQuotePrice(new \DateTime());
      }

      $entity->setRetainage($retainage);
      $valRetPct = ($retainage_percentage !== '' && $retainage_percentage !== null) ? (float)$retainage_percentage : null;
      $entity->setRetainagePercentage($valRetPct);
      $valRetAdjPct = ($retainage_adjustment_percentage !== '' && $retainage_adjustment_percentage !== null) ? (float)$retainage_adjustment_percentage : null;
      $entity->setRetainageAdjustmentPercentage($valRetAdjPct);
      $valRetAdjComp = ($retainage_adjustment_completion !== '' && $retainage_adjustment_completion !== null) ? (float)$retainage_adjustment_completion : null;
      $entity->setRetainageAdjustmentCompletion($valRetAdjComp);


      $entity->setPrevailingWage($prevailing_wage);
      $valPrevRate = ($prevailing_rate !== '' && $prevailing_rate !== null) ? (float)$prevailing_rate : null;
      $entity->setPrevailingRate($valPrevRate);
         if ($prevailing_county_id != '') {
         $prevailing_county = $this->getDoctrine()->getRepository(County::class)
            ->find($prevailing_county_id);
         $entity->setPrevailingCounty($prevailing_county);
      }

      if ($prevailing_role_id != '') {
         $prevailing_role = $this->getDoctrine()->getRepository(EmployeeRole::class)
            ->find($prevailing_role_id);
         $entity->setPrevailingRole($prevailing_role);
      }

      $entity->setCreatedAt(new \DateTime());

      $em->persist($entity);


      // items
      $items_new = $this->SalvarItems($entity, $items);

      // save contacts
      $this->SalvarContacts($entity, $contacts);

      // save concrete classes
      $this->SalvarConcreteClasses($entity, $concrete_classes);

      // counties
      $this->SalvarCounties($entity, $county_ids, false);

      $em->flush();

      //Salvar log
      $log_operacion = "Add";
      $log_categoria = "Project";
      $log_descripcion = "The project is added: $name";
      $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

      $resultado['success'] = true;
      $resultado['project_id'] = $entity->getProjectId();
      $resultado['items'] = $items_new;

      return $resultado;
   }

   /**
    * SalvarArchivos
    * @param $archivos
    * @param Project $entity
    * @return void
    */
   public function SalvarArchivos($entity, $archivos)
   {
      $em = $this->getDoctrine()->getManager();

      foreach ($archivos as $value) {

         $archivo_entity = null;

         if (is_numeric($value->id)) {
            $archivo_entity = $this->getDoctrine()->getRepository(ProjectAttachment::class)
               ->find($value->id);
         }

         $is_new_archivo = false;
         if ($archivo_entity == null) {
            $archivo_entity = new ProjectAttachment();
            $is_new_archivo = true;
         }

         $archivo_entity->setName($value->name);
         $archivo_entity->setFile($value->file);

         if ($is_new_archivo) {
            $archivo_entity->setProject($entity);

            $em->persist($archivo_entity);
         }
      }
   }

   /**
    * SalvarAjustesPrecio
    * @param $ajustes_precio
    * @param Project $entity
    * @return void
    */
   public function SalvarAjustesPrecio($entity, $ajustes_precio)
   {
      $em = $this->getDoctrine()->getManager();

      foreach ($ajustes_precio as $value) {

         $ajuste_entity = null;

         if (is_numeric($value->id)) {
            $ajuste_entity = $this->getDoctrine()->getRepository(ProjectPriceAdjustment::class)
               ->find($value->id);
         }

         $is_new_ajuste = false;
         if ($ajuste_entity == null) {
            $ajuste_entity = new ProjectPriceAdjustment();
            $is_new_ajuste = true;
         }

         $ajuste_entity->setPercent($value->percent);

         if ($value->day != '') {
            $day = \DateTime::createFromFormat('m/d/Y', $value->day);
            $ajuste_entity->setDay($day);
         }

         // Guardar items_id (puede ser vacío para todos los items)
         $items_id = isset($value->items_id) ? $value->items_id : '';
         $ajuste_entity->setItemsId($items_id);


         if ($is_new_ajuste) {
            $ajuste_entity->setProject($entity);

            $em->persist($ajuste_entity);
         }
      }
   }

   /**
    * SalvarCounties
    * @param Project $entity
    * @param array|string $county_ids Array de IDs de counties o string separado por comas
    * @param bool $check_changes Si es true, compara con counties existentes y retorna información de cambios
    * @return array Información de cambios si check_changes es true, array vacío si es false
    */
   public function SalvarCounties($entity, $county_ids, $check_changes = false)
   {
      $countyRepo = $this->getDoctrine()->getRepository(County::class);
      $projectCountyRepo = $this->getDoctrine()->getRepository(ProjectCounty::class);
      $em = $this->getDoctrine()->getManager();

      $result = [
         'changed' => false,
         'old_descriptions' => ''
      ];

      // Convertir $county_ids a array si viene como string o array
      if (is_string($county_ids)) {
         $county_ids = !empty($county_ids) ? explode(',', $county_ids) : [];
      }
      if (!is_array($county_ids)) {
         $county_ids = [];
      }
      $county_ids = array_filter(array_map('trim', $county_ids));

      // Si check_changes es true, obtener counties antiguos para comparación
      if ($check_changes && $entity->getProjectId()) {
         $projectCounties_old = $projectCountyRepo->ListarCountysDeProject($entity->getProjectId());
         $county_ids_old = [];
         $county_descriptions_old = [];
         foreach ($projectCounties_old as $projectCounty) {
            $county = $projectCounty->getCounty();
            if ($county !== null) {
               $county_ids_old[] = $county->getCountyId();
               $county_descriptions_old[] = $county->getDescription();
            }
         }

         // Comparar cambios
         sort($county_ids_old);
         sort($county_ids);
         if ($county_ids_old != $county_ids) {
            $result['changed'] = true;
            $result['old_descriptions'] = implode(', ', $county_descriptions_old);
         }
      }

      // Eliminar counties existentes (solo si el proyecto ya existe)
      if ($entity->getProjectId()) {
         $projectCountyRepo->EliminarCountysDeProject($entity->getProjectId());
      }

      // Agregar nuevos counties
      foreach ($county_ids as $county_id) {
         if (!empty($county_id)) {
            $county = $countyRepo->find($county_id);
            if ($county !== null) {
               $projectCounty = new ProjectCounty();
               $projectCounty->setProject($entity);
               $projectCounty->setCounty($county);
               $em->persist($projectCounty);
            }
         }
      }

      return $result;
   }

   /**
    * SalvarContacts
    * Contacts reference CompanyContact (company_contact_id). When set, name/email/phone come from there.
    * Skips contacts without company_contact_id. Legacy records (company_contact_id NULL) persist unchanged.
    *
    * @param $contacts
    * @param Project $entity
    * @return void
    */
   public function SalvarContacts($entity, $contacts)
   {
      $em = $this->getDoctrine()->getManager();

      foreach ($contacts as $value) {
         if (empty($value->company_contact_id)) {
            continue;
         }

         $contact_entity = null;

         if (!empty($value->contact_id) && is_numeric($value->contact_id)) {
            $contact_entity = $this->getDoctrine()->getRepository(ProjectContact::class)
               ->find($value->contact_id);
         }

         $is_new_contact = false;
         if ($contact_entity == null) {
            $contact_entity = new ProjectContact();
            $is_new_contact = true;
         }

         /** @var CompanyContact|null $companyContact */
         $companyContact = $this->getDoctrine()->getRepository(CompanyContact::class)
            ->find($value->company_contact_id);

         if ($companyContact) {
            $contact_entity->setCompanyContact($companyContact);
            // name/email/phone stay empty in DB; entity getters use CompanyContact
         }

         $contact_entity->setRole($value->role ?? null);
         $contact_entity->setNotes($value->notes ?? null);

         if ($is_new_contact) {
            $contact_entity->setProject($entity);
            $em->persist($contact_entity);
         }
      }
   }

   /**
    * SalvarConcreteClasses
    * @param $concrete_classes
    * @param Project $entity
    * @return void
    */
   public function SalvarConcreteClasses($entity, $concrete_classes)
   {
      $em = $this->getDoctrine()->getManager();

      if (!is_array($concrete_classes)) {
         return;
      }

      foreach ($concrete_classes as $value) {
         $concrete_class_entity = null;

         if (is_numeric($value->id)) {
            $concrete_class_entity = $this->getDoctrine()->getRepository(ProjectConcreteClass::class)
               ->find($value->id);
         }

         $is_new_concrete_class = false;
         if ($concrete_class_entity == null) {
            $concrete_class_entity = new ProjectConcreteClass();
            $is_new_concrete_class = true;
         }

         if ($value->concrete_class_id != '') {
            $concrete_class = $this->getDoctrine()->getRepository(ConcreteClass::class)
               ->find($value->concrete_class_id);
            $concrete_class_entity->setConcreteClass($concrete_class);
         }

         $concrete_quote_price = ($value->concrete_quote_price !== '' && $value->concrete_quote_price !== null) ? (float)$value->concrete_quote_price : null;
         $concrete_class_entity->setConcreteQuotePrice($concrete_quote_price);

         if ($is_new_concrete_class) {
            $concrete_class_entity->setProject($entity);
            $em->persist($concrete_class_entity);
         }
      }
   }

   /**
    * SalvarItems
    * @param array $items
    * @param Project $entity
    * @return array
    */
   public function SalvarItems($entity, $items)
   {
      $em = $this->getDoctrine()->getManager();

      // para devolver los items nuevos que se creen
      $items_news = [];

      //Senderos
      foreach ($items as $value) {

         $project_item_entity = null;

         if (is_numeric($value->project_item_id)) {
            $project_item_entity = $this->getDoctrine()->getRepository(ProjectItem::class)
               ->find($value->project_item_id);
         }

         $is_new_project_item = false;
         if ($project_item_entity == null) {
            $project_item_entity = new ProjectItem();
            $is_new_project_item = true;
         }

         // Guardar valores antiguos antes de actualizar (solo si no es nuevo)
         $quantity_old = null;
         $price_old = null;
         if (!$is_new_project_item && $project_item_entity->getId()) {
            $quantity_old = $project_item_entity->getQuantity();
            $price_old = $project_item_entity->getPrice();
         }

         $project_item_entity->setYieldCalculation($value->yield_calculation);
         $project_item_entity->setPrice($value->price);
         $project_item_entity->setQuantity($value->quantity);

         // Verificar si se está desactivando el change order
         $change_order_old = $project_item_entity->getChangeOrder();
         $project_item_entity->setChangeOrder($value->change_order);

         if ($value->change_order) {
            // Si se activa el change order, establecer la fecha si se proporciona
            if ($value->change_order_date != '') {
               $change_order_date = \DateTime::createFromFormat('m/d/Y', $value->change_order_date);
               $project_item_entity->setChangeOrderDate($change_order_date);
            }
         } else {
            // Si se desactiva el change order, establecer la fecha en null y eliminar el historial
            $project_item_entity->setChangeOrderDate(null);
            if ($change_order_old && $project_item_entity->getId()) {
               // Eliminar historial solo si antes estaba activo
               $this->EliminarHistorialDeProjectItem($project_item_entity->getId());
            }
         }

         $equation_entity = null;
         if ($value->equation_id != '') {
            $equation_entity = $this->getDoctrine()->getRepository(Equation::class)->find($value->equation_id);
            $project_item_entity->setEquation($equation_entity);
         }

         $item_entity = null;
         if ($value->item_id != '') {
            $item_entity = $this->getDoctrine()->getRepository(Item::class)->find($value->item_id);
         } else {
            // add new item
            $item_entity = $this->AgregarNewItem($value, $equation_entity);
            $items_news[] = [
               'item_id' => $item_entity->getItemId(),
               'name' => $item_entity->getName(),
               'price' => $value->price,
               'unit' => $value->unit,
               'equation' => $value->equation_id,
               'yield' => $value->yield_calculation,
               'change_order' => $value->change_order,
               'change_order_date' => $value->change_order ? new \DateTime() : null,
            ];
         }

         $project_item_entity->setItem($item_entity);

         if ($is_new_project_item) {
            $project_item_entity->setProject($entity);

            $em->persist($project_item_entity);
            $em->flush(); // Flush para obtener el ID
         } else {
            // Guardar valores antiguos antes de actualizar
            $quantity_old = $project_item_entity->getQuantity();
            $price_old = $project_item_entity->getPrice();
         }

         // Registrar historial: para change order (add + cambios), para el resto solo cambios de cantidad/precio
         if ($value->change_order && $project_item_entity->getId()) {
            $is_first_time_change_order = !$change_order_old && $value->change_order;
            $this->RegistrarHistorialChangeOrder($project_item_entity, $is_new_project_item, $is_first_time_change_order, $quantity_old, $value->quantity, $price_old, $value->price);
         } elseif (!$is_new_project_item && isset($quantity_old, $price_old) && ($quantity_old != $value->quantity || $price_old != $value->price)) {
            $this->RegistrarHistorialChangeOrder($project_item_entity, false, false, $quantity_old, $value->quantity, $price_old, $value->price);
         }
      }

      $em->flush();

      return $items_news;
   }

   public function ActualizarRetainageItems(array $ids, $status)
   {
      /** @var \App\Repository\ProjectItemRepository $repo */
      $repo = $this->getDoctrine()->getRepository(\App\Entity\ProjectItem::class);

      return $repo->ActualizarRetainageMasivo($ids, (bool)$status);
   }

   public function ActualizarBonedItems(array $ids, $status)
   {
      /** @var \App\Repository\ProjectItemRepository $repo */
      $repo = $this->getDoctrine()->getRepository(\App\Entity\ProjectItem::class);

      return $repo->ActualizarBondedMasivo($ids, (bool)$status);
   }


   /**
    * ListarProjects: Listar los projects
    *
    * @param int $start Inicio
    * @param int $limit Limite
    * @param string $sSearch Para buscar
    *
    * @author Marcel
    */
   public function ListarProjects(
      $start,
      $limit,
      $sSearch,
      $iSortCol_0,
      $sSortDir_0,
      $company_id,
      $status,
      $fecha_inicial,
      $fecha_fin
   ) {
      $arreglo_resultado = array();
      $cont = 0;

      $projects = [];

      if ($sSearch != '') {
         /** @var ProjectItemRepository $projectItemRepo */
         $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
         $lista = $projectItemRepo->ListarProjects(
            $start,
            $limit,
            $sSearch,
            $iSortCol_0,
            $sSortDir_0,
            $company_id,
            '',
            $status,
            $fecha_inicial,
            $fecha_fin
         );
         foreach ($lista as $p_i) {
            $projects[] = $p_i->getProject();
         }

         // si no encontro buscar en projects
         if (empty($projects)) {
            /** @var ProjectRepository $projectRepo */
            $projectRepo = $this->getDoctrine()->getRepository(Project::class);
            $projects = $projectRepo->ListarProjects(
               $start,
               $limit,
               $sSearch,
               $iSortCol_0,
               $sSortDir_0,
               $company_id,
               '',
               $status,
               $fecha_inicial,
               $fecha_fin
            );
         }
      } else {
         /** @var ProjectRepository $projectRepo */
         $projectRepo = $this->getDoctrine()->getRepository(Project::class);
         $projects = $projectRepo->ListarProjects(
            $start,
            $limit,
            $sSearch,
            $iSortCol_0,
            $sSortDir_0,
            $company_id,
            '',
            $status,
            $fecha_inicial,
            $fecha_fin
         );
      }

      foreach ($projects as $value) {
         $project_id = $value->getProjectId();

         $acciones = $this->ListarAcciones($project_id);

         // listar ultima nota del proyecto
         $nota = $this->ListarUltimaNotaDeProject($project_id);

         $arreglo_resultado[$cont] = array(
            "id" => $project_id,
            "projectNumber" => $value->getProjectNumber(),
            "subcontract" => $value->getSubcontract(),
            "name" => $value->getName(),
            "description" => $value->getDescription(),
            "company" => $value->getCompany()->getName(),
            "county" => $this->getCountiesDescriptionForProject($value),
            "status" => $value->getStatus(),
            "startDate" => $value->getStartDate() != '' ? $value->getStartDate()->format('m/d/Y') : '',
            "endDate" => $value->getEndDate() != '' ? $value->getEndDate()->format('m/d/Y') : '',
            "dueDate" => $value->getDueDate() != '' ? $value->getDueDate()->format('m/d/Y') : '',
            'nota' => $nota,
            "acciones" => $acciones
         );


         $cont++;
      }

      return $arreglo_resultado;
   }

   /**
    * TotalProjects: Total de projects
    * @param string $sSearch Para buscar
    * @author Marcel
    */
   public function TotalProjects($sSearch, $company_id, $status, $fecha_inicial, $fecha_fin)
   {
      if ($sSearch != '') {
         /** @var ProjectItemRepository $projectItemRepo */
         $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
         $total = $projectItemRepo->TotalProjects($sSearch, $company_id, '', $status, $fecha_inicial, $fecha_fin);
      } else {
         /** @var ProjectRepository $projectRepo */
         $projectRepo = $this->getDoctrine()->getRepository(Project::class);
         $total = $projectRepo->TotalProjects($sSearch, $company_id, '', $status, $fecha_inicial, $fecha_fin);
      }


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
      $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 9);

      $acciones = '<a href="javascript:;" class="view m-portlet__nav-link btn m-btn m-btn--hover-info m-btn--icon m-btn--icon-only m-btn--pill" title="View record" data-id="' . $id . '"> <i class="la la-eye"></i> </a> ';

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

   /**
    * RegistrarHistorialChangeOrder: Registra el historial de cambios para items con change order
    * @param ProjectItem $project_item_entity
    * @param bool $is_new
    * @param bool $is_first_time_change_order Si es la primera vez que se activa el change order
    * @param float|null $quantity_old
    * @param float $quantity_new
    * @param float|null $price_old
    * @param float $price_new
    * @return void
    */
   private function RegistrarHistorialChangeOrder($project_item_entity, $is_new, $is_first_time_change_order, $quantity_old, $quantity_new, $price_old, $price_new)
   {
      $em = $this->getDoctrine()->getManager();

      // Obtener la fecha del change order date, si no existe usar la fecha actual
      $change_order_date = $project_item_entity->getChangeOrderDate();
      if ($change_order_date === null) {
         $change_order_date = new \DateTime();
      }

      // Si es nuevo item O si se está activando change order por primera vez
      if ($is_new || $is_first_time_change_order) {
         $history = new ProjectItemHistory();
         $history->setProjectItem($project_item_entity);
         $history->setActionType('add');
         $history->setOldValue(null);
         $history->setNewValue(null);
         $history->setCreatedAt($change_order_date);
         $history->setUser($this->getUser());
         $em->persist($history);
      }

      // Si cambió la cantidad - created_at = fecha actual del cambio (no change_order_date)
      if ($quantity_old !== null && $quantity_old != $quantity_new) {
         $history = new ProjectItemHistory();
         $history->setProjectItem($project_item_entity);
         $history->setActionType('update_quantity');
         $history->setOldValue((string)$quantity_old);
         $history->setNewValue((string)$quantity_new);
         $history->setCreatedAt(new \DateTime());
         $history->setUser($this->getUser());
         $em->persist($history);
      }

      // Si cambió el precio - created_at = fecha actual del cambio (no change_order_date)
      if ($price_old !== null && $price_old != $price_new) {
         $history = new ProjectItemHistory();
         $history->setProjectItem($project_item_entity);
         $history->setActionType('update_price');
         $history->setOldValue((string)$price_old);
         $history->setNewValue((string)$price_new);
         $history->setCreatedAt(new \DateTime());
         $history->setUser($this->getUser());
         $em->persist($history);
      }
   }

   /**
    * ListarHistorialDeItem: Lista el historial de cambios de un ProjectItem
    * @param int $project_item_id
    * @return array
    */
   public function ListarHistorialDeItem($project_item_id)
   {
      $historial = [];

      /** @var ProjectItemHistoryRepository $historyRepo */
      $historyRepo = $this->getDoctrine()->getRepository(ProjectItemHistory::class);
      $lista = $historyRepo->ListarHistorialDeItem($project_item_id);

      foreach ($lista as $value) {
         $user_name = $value->getUser() ? $value->getUser()->getNombreCompleto() : 'Unknown';
         $fecha = $value->getCreatedAt()->format('m/d/Y');
         $action_type = $value->getActionType();
         $old_value = $value->getOldValue();
         $new_value = $value->getNewValue();

         $mensaje = '';
         if ($action_type === 'add') {
            $mensaje = "Add on {$fecha} by \"{$user_name}\"";
         } elseif ($action_type === 'update_quantity') {
            $mensaje = "{$fecha} Updated qty from \"{$old_value}\" to \"{$new_value}\" by \"{$user_name}\"";
         } elseif ($action_type === 'update_price') {
            $mensaje = "{$fecha} Updated price from \"{$old_value}\" to \"{$new_value}\" by \"{$user_name}\"";
         }

         $historial[] = [
            'id' => $value->getId(),
            'action_type' => $action_type,
            'mensaje' => $mensaje,
            'fecha' => $fecha,
            'user_name' => $user_name,
            'old_value' => $old_value,
            'new_value' => $new_value,
         ];
      }

      return $historial;
   }

   /**
    * ListarInvoicesConRetainage: Lista los invoices de un proyecto con sus cálculos de retainage
    * @param int $project_id
    * @return array
    */

   public function ListarInvoicesConRetainage($project_id)
   {
      if (empty($project_id)) {
         return [];
      }

      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
      $project = $this->getDoctrine()->getRepository(Project::class)->find($project_id);

      if (!$project || !$project->getRetainage()) {
         return [];
      }

      $invoices = $invoiceRepo->ListarInvoicesDeProject($project_id);

      // 1. Ordenamos por fecha para que el acumulado histórico sea cronológico
      usort($invoices, function ($a, $b) {
         return $a->getCreatedAt() <=> $b->getCreatedAt();
      });

      $resultado = [];
      $running_balance = 0;

      $retainage_percentage = (float)$project->getRetainagePercentage();
      $retainage_adjustment_percentage = (float)$project->getRetainageAdjustmentPercentage();
      $retainage_adjustment_completion = (float)$project->getRetainageAdjustmentCompletion();

      foreach ($invoices as $invoice) {
         $invoice_id_str = (string) $invoice->getInvoiceId();

         // Valores visuales generales de la factura
         $invoice_amount = $invoiceItemRepo->TotalInvoiceFinalAmountThisPeriodRetainageOnly($invoice_id_str);
         $paid_amount_total_invoice = $invoiceItemRepo->TotalInvoicePaidAmount($invoice_id_str);

         // Inv. Ret Amt = mismo valor que la caja "Current Retainage" del invoice
         $retainage_efectivo = $this->invoiceService->CalcularRetainageEfectivoParaInvoice($invoice_id_str);
         $retainage_entry = (float) $retainage_efectivo['effective_current'];

         // Porcentaje para visualización: derivado del cálculo del invoice (base = Invoice Amt)
         $porciento_retainage = ($invoice_amount > 0 && $retainage_entry > 0)
            ? ($retainage_entry / $invoice_amount * 100)
            : $retainage_percentage;

         // Ajuste aplicado: inferido si se usó el porcentaje de ajuste (umbral basado en billed, no paid)
         $ajuste_aplicado = ($retainage_adjustment_completion > 0 && abs($porciento_retainage - $retainage_adjustment_percentage) < 0.01);

         // Manejo de Reembolsos
         $reimbursed_real = 0;
         foreach ($invoice->getReimbursementHistories() as $history) {
            $reimbursed_real += (float)$history->getAmount();
         }

         $running_balance += $retainage_entry;
         $running_balance -= $reimbursed_real;
         $saldo_visual_fila = $running_balance;

         $resultado[] = [
            'invoice_id' => $invoice->getInvoiceId(),
            'invoice_number' => $invoice->getNumber(),
            'invoice_date' => $invoice->getCreatedAt()->format('m/d/Y'),
            'invoice_amount' => $invoice_amount,
            'paid_amount' => $paid_amount_total_invoice, // Mostramos lo que se pagó en total
            'paid' => $invoice->getPaid() ? 1 : 0,
            'retainage_percentage' => $porciento_retainage,
            'inv_ret_amt' => $retainage_entry,
            'retainage_amount' => $retainage_entry,
            'paid_ret_amt' => $retainage_entry, // Columna clave calculada correctamente
            'total_retainage_to_date' => $saldo_visual_fila,
            'ajuste_retainage' => $ajuste_aplicado ? 'Yes' : 'No',
            'retainage_reimbursed' => ($reimbursed_real > 0) ? 1 : 0,
            'reimbursed_amount' => $reimbursed_real,
            'startDate' => $invoice->getStartDate() ? $invoice->getStartDate()->format('Y-m-d') : '',
            'endDate' => $invoice->getEndDate() ? $invoice->getEndDate()->format('Y-m-d') : '',
            'reimbursed_date' => $invoice->getRetainageReimbursedDate() ? $invoice->getRetainageReimbursedDate()->format('m/d/Y') : ''
         ];
      }

      // Revertimos para mostrar la más reciente arriba 
      return array_reverse($resultado);
   }
}
