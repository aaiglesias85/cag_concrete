<?php

namespace App\Utils\Admin;

use App\Entity\InvoiceItemNotes;
use App\Entity\InvoiceNotes;
use App\Entity\Project;
use App\Entity\Invoice;
use App\Entity\InvoiceItem;

use App\Entity\InvoiceAttachment;
use App\Repository\InvoiceAttachmentRepository;
use App\Repository\InvoiceItemRepository;
use App\Repository\InvoiceNotesRepository;
use App\Repository\InvoiceRepository;
use App\Repository\ProjectRepository;
use App\Utils\Base;
use Google\Type\Date;

class PaymentService extends Base
{

   /**
    * EliminarNotesItem: Elimina un notes en la BD
    * @param int $notes_id Id
    * @author Marcel
    */
   public function EliminarNotesItem($notes_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(InvoiceItemNotes::class)
         ->find($notes_id);
      /**@var InvoiceItemNotes $entity */
      if ($entity != null) {
         $notes = $entity->getNotes();
         $project_entity = $entity->getInvoiceItem()->getInvoice()->getProject();
         $invoice_number = $entity->getInvoiceItem()->getInvoice()->getNumber();
         $item_name = $entity->getInvoiceItem()->getProjectItem()->getItem()->getName();

         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Invoice Notes";
         $log_descripcion = "Notes '$notes' have been deleted to invoice #$invoice_number (Project: {$project_entity->getName()}) (Item: {$item_name})";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * SalvarNotesItem
    * @param $notes_id
    * @param $invoice_item_id
    * @param $notes
    * @return array
    */
   public function SalvarNotesItem($notes_id, $invoice_item_id, $notes)
   {

      $em = $this->getDoctrine()->getManager();

      $invoice_item_entity = $this->getDoctrine()->getRepository(InvoiceItem::class)
         ->find($invoice_item_id);
      /** @var InvoiceItem $invoice_item_entity */
      if ($invoice_item_entity != null) {

         $project_entity = $invoice_item_entity->getInvoice()->getProject();
         $invoice_number = $invoice_item_entity->getInvoice()->getNumber();
         $item_name = $invoice_item_entity->getProjectItem()->getItem()->getName();

         $entity = null;
         $is_new = false;

         if (is_numeric($notes_id)) {
            $entity = $this->getDoctrine()->getRepository(InvoiceItemNotes::class)
               ->find($notes_id);
         }

         if ($entity == null) {
            $entity = new InvoiceItemNotes();
            $is_new = true;
         }

         $entity->setNotes($notes);



         $log_operacion = "Add";
         $log_descripcion = "Notes '$notes' have been added to invoice #$invoice_number (Project: {$project_entity->getName()}) (Item: {$item_name})";

         if ($is_new) {

            $entity->setDate(new \DateTime());
            $entity->setInvoiceItem($invoice_item_entity);

            $em->persist($entity);
         } else {
            $log_operacion = "Update";
            $log_descripcion = "Notes '$notes' have been updated to invoice #$invoice_number (Project: {$project_entity->getName()}) (Item: {$item_name})";
         }

         $em->flush();

         //Salvar log
         $log_categoria = "Invoice Notes";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
         $resultado['note'] = [
            'id' => $entity->getId(),
            'notes' => mb_convert_encoding($notes, 'UTF-8', 'UTF-8'),
            'date' => $entity->getDate()->format('m/d/Y')
         ];
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The project not exist.";
      }

      return $resultado;
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
         $dir = 'uploads/invoice/';
         if (is_file($dir . $archivo)) {
            unlink($dir . $archivo);
         }

         $em = $this->getDoctrine()->getManager();

         $archivo_entity = $this->getDoctrine()->getRepository(InvoiceAttachment::class)
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
      $dir = 'uploads/invoice/';
      if (is_file($dir . $archivo)) {
         unlink($dir . $archivo);
      }

      $em = $this->getDoctrine()->getManager();

      $archivo_entity = $this->getDoctrine()->getRepository(InvoiceAttachment::class)
         ->findOneBy(array('file' => $archivo));
      if ($archivo_entity != null) {
         $em->remove($archivo_entity);
      }

      $em->flush();

      $resultado['success'] = true;
      return $resultado;
   }

   /**
    * EliminarNotes: Elimina un notes en la BD
    * @param int $notes_id Id
    * @author Marcel
    */
   public function EliminarNotes($notes_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(InvoiceNotes::class)
         ->find($notes_id);
      /**@var InvoiceNotes $entity */
      if ($entity != null) {
         $notes = $entity->getNotes();
         $invoice_number = $entity->getInvoice()->getNumber();

         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Invoice Notes";
         $log_descripcion = "The notes: $notes is delete from invoice #: $invoice_number";
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
    * @param int $invoice_id Id
    * @author Marcel
    */
   public function EliminarNotesDate($invoice_id, $from, $to)
   {
      $em = $this->getDoctrine()->getManager();

      $invoice_entity = $this->getDoctrine()->getRepository(Invoice::class)
         ->find($invoice_id);
      /** @var Invoice $invoice_entity */
      if ($invoice_entity != null) {

         $invoice_number = $invoice_entity->getNumber();


         /** @var InvoiceNotesRepository $invoiceNotesRepo */
         $invoiceNotesRepo = $this->getDoctrine()->getRepository(InvoiceNotes::class);
         $notes = $invoiceNotesRepo->ListarNotesDeInvoice($invoice_id, $from, $to);
         foreach ($notes as $entity) {
            $em->remove($entity);
         }

         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Invoice Notes";
         $log_descripcion = "The notes $from and $to is delete from invoice #: $invoice_number";
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

      $entity = $this->getDoctrine()->getRepository(InvoiceNotes::class)
         ->find($notes_id);
      /** @var InvoiceNotes $entity */
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
    * @param $invoice_id
    * @param $notes
    * @param $date
    * @return array
    */
   public function SalvarNotes($notes_id, $invoice_id, $notes, $date)
   {

      $em = $this->getDoctrine()->getManager();

      $invoice_entity = $this->getDoctrine()->getRepository(Invoice::class)
         ->find($invoice_id);
      /** @var Invoice $invoice_entity */
      if ($invoice_entity != null) {

         $project_entity = $invoice_entity->getProject();
         $invoice_number = $invoice_entity->getNumber();

         $entity = null;
         $is_new = false;

         if (is_numeric($notes_id)) {
            $entity = $this->getDoctrine()->getRepository(InvoiceNotes::class)
               ->find($notes_id);
         }

         if ($entity == null) {
            $entity = new InvoiceNotes();
            $is_new = true;
         }

         $entity->setNotes($notes);

         if ($date != '') {
            $date = \DateTime::createFromFormat('m/d/Y', $date);
            $entity->setDate($date);
         }

         $entity->setInvoice($invoice_entity);

         $log_operacion = "Add";
         $log_descripcion = "Notes '$notes' have been added to invoice #$invoice_number (Project: {$project_entity->getName()})";

         if ($is_new) {
            $em->persist($entity);
         } else {
            $log_operacion = "Update";
            $log_descripcion = "Notes '$notes' have been updated to invoice #$invoice_number (Project: {$project_entity->getName()})";
         }

         $em->flush();

         //Salvar log
         $log_categoria = "Invoice Notes";
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
      /** @var InvoiceNotesRepository $invoiceNotesRepo */
      $invoiceNotesRepo = $this->getDoctrine()->getRepository(InvoiceNotes::class);
      $resultado = $invoiceNotesRepo->ListarNotesConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $fecha_inicial, $fecha_fin);

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
    * CargarDatosPayment: Carga los datos de un invoice
    *
    * @param int $invoice_id Id
    *
    * @author Marcel
    */
   public function CargarDatosPayment($invoice_id)
   {
      $resultado = array();
      $arreglo_resultado = array();

      $entity = $this->getDoctrine()->getRepository(Invoice::class)
         ->find($invoice_id);
      /** @var Invoice $entity */
      if ($entity != null) {

         $arreglo_resultado['project_id'] = $entity->getProject()->getProjectId();

         $company_id = $entity->getProject()->getCompany()->getCompanyId();
         $arreglo_resultado['company_id'] = $company_id;

         $arreglo_resultado['number'] = $entity->getNumber();
         $arreglo_resultado['start_date'] = $entity->getStartDate()->format('m/d/Y');
         $arreglo_resultado['end_date'] = $entity->getEndDate()->format('m/d/Y');
         $arreglo_resultado['notes'] = $entity->getNotes();
         $arreglo_resultado['paid'] = $entity->getPaid();

         // projects
         $projects = $this->ListarProjectsDeCompany($company_id);
         $arreglo_resultado['projects'] = $projects;

         // items
         $items = $this->ListarItemsDeInvoice($invoice_id);
         $arreglo_resultado['items'] = $items;

         // payments
         $payments = $this->ListarPaymentsDeInvoice($invoice_id);
         $arreglo_resultado['payments'] = $payments;

         // archivos
         $archivos = $this->ListarArchivosDeInvoice($invoice_id);
         $arreglo_resultado['archivos'] = $archivos;

         $resultado['success'] = true;
         $resultado['payment'] = $arreglo_resultado;
      }

      return $resultado;
   }

   /**
    * ListarArchivosDeInvoice
    * @param $invoice_id
    * @return array
    */
   public function ListarArchivosDeInvoice($invoice_id)
   {
      $archivos = [];

      /** @var InvoiceAttachmentRepository $invoiceAttachmentRepo */
      $invoiceAttachmentRepo = $this->getDoctrine()->getRepository(InvoiceAttachment::class);
      $project_archivos = $invoiceAttachmentRepo->ListarAttachmentsDeInvoice($invoice_id);
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
    * ListarItemsDeInvoice
    * @param $invoice_id
    * @return array
    */
   public function ListarItemsDeInvoice($invoice_id)
   {
      $items = [];

      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
      $lista = $invoiceItemRepo->ListarItems($invoice_id);
      foreach ($lista as $key => $value) {

         $contract_qty = $value->getProjectItem()->getQuantity();
         $price = $value->getPrice();
         $contract_amount = $contract_qty * $price;

         $quantity_from_previous = $value->getQuantityFromPrevious();
         $unpaid_from_previous = $value->getUnpaidFromPrevious();

         $quantity = $value->getQuantity();

         $quantity_completed = $quantity + $quantity_from_previous;

         $amount = $quantity * $price;

         $total_amount = $quantity_completed * $price;

         $items[] = [
            "invoice_item_id" => $value->getId(),
            "project_item_id" => $value->getProjectItem()->getId(),
            "item_id" => $value->getProjectItem()->getItem()->getItemId(),
            "item" => $value->getProjectItem()->getItem()->getName(),
            "unit" => $value->getProjectItem()->getItem()->getUnit() != null ? $value->getProjectItem()->getItem()->getUnit()->getDescription() : '',
            "contract_qty" => $contract_qty,
            "price" => $price,
            "contract_amount" => $contract_amount,
            "quantity_from_previous" => $quantity_from_previous,
            "unpaid_from_previous" => $unpaid_from_previous,
            "quantity" => $quantity,
            "quantity_completed" => $quantity_completed,
            "amount" => $amount,
            "total_amount" => $total_amount,
            "principal" => $value->getProjectItem()->getPrincipal(),
            "posicion" => $key
         ];
      }

      return $items;
   }

   /**
    * ListarProjectsDeCompany
    * @param $company_id
    * @return array
    */
   public function ListarProjectsDeCompany($company_id)
   {
      $projects = [];

      /** @var ProjectRepository $projectRepo */
      $projectRepo = $this->getDoctrine()->getRepository(Project::class);
      $lista = $projectRepo->ListarOrdenados('', $company_id, '');
      foreach ($lista as $value) {
         $projects[] = [
            'project_id' => $value->getProjectId(),
            'number' => $value->getProjectNumber(),
            'name' => $value->getName(),
            'description' => $value->getDescription()
         ];
      }

      return $projects;
   }

   /**
    * ActualizarPayment: Actuializa los datos del rol en la BD
    * @param int $invoice_id Id
    * @author Marcel
    */
   public function ActualizarPayment($invoice_id, $payments, $archivos)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Invoice::class)
         ->find($invoice_id);
      /** @var Invoice $entity */
      if ($entity != null) {

         $entity->setUpdatedAt(new \DateTime());

         // items
         $this->SalvarPayments($entity, $payments);

         // save archivos
         $this->SalvarArchivos($entity, $archivos);

         $em->flush();

         //Salvar log
         $log_operacion = "Update";
         $log_categoria = "Invoice";

         $number = $entity->getNumber();
         $log_descripcion = "The invoice #$number is modified";

         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;

         return $resultado;
      }
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
            $archivo_entity = $this->getDoctrine()->getRepository(InvoiceAttachment::class)
               ->find($value->id);
         }

         $is_new_archivo = false;
         if ($archivo_entity == null) {
            $archivo_entity = new InvoiceAttachment();
            $is_new_archivo = true;
         }

         $archivo_entity->setName($value->name);
         $archivo_entity->setFile($value->file);

         if ($is_new_archivo) {
            $archivo_entity->setInvoice($entity);

            $em->persist($archivo_entity);
         }
      }
   }

   /**
    * SalvarPayments
    * @param array $payments
    * @param Invoice $entity
    * @return void
    */
   public function SalvarPayments($entity, $payments)
   {
      $invoice_id = $entity->getInvoiceId();
      $project_id = $entity->getProject()->getProjectId();

      // Guardar los project_item_ids que se están actualizando para recalcular invoices siguientes
      $updated_project_item_ids = [];

      //items
      $paid = false;
      foreach ($payments as $value) {

         /** @var InvoiceItemRepository $invoiceItemRepo */
         $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
         $invoice_item_entity = $invoiceItemRepo->BuscarItem($invoice_id, $value->project_item_id);
         if ($invoice_item_entity != null) {
            // Guardar project_item_id para actualizar invoices siguientes
            $updated_project_item_ids[] = $value->project_item_id;

            // payment
            $invoice_item_entity->setPaidQty($value->paid_qty);
            $invoice_item_entity->setUnpaidQty($value->unpaid_qty);
            $invoice_item_entity->setPaidAmount($value->paid_amount);
            $invoice_item_entity->setPaidAmountTotal($value->paid_amount_total);
         }

         // si se paga al menos 1 item, marcar el invoice como paid
         if ($value->paid_qty > 0 || $value->paid_amount > 0 || $value->paid_amount_total > 0) {
            $paid = true;
         }
      }

      // paid invoice - si se paga al menos un item, marcar como paid
      if (!empty($payments) && $paid && !$entity->getPaid()) {
         $entity->setPaid(true);
      }

      // Actualizar unpaid_from_previous en invoices siguientes
      if (!empty($updated_project_item_ids)) {
         $this->ActualizarUnpaidFromPreviousEnInvoicesSiguientes($entity, $updated_project_item_ids);
      }
   }

   /**
    * ActualizarUnpaidFromPreviousEnInvoicesSiguientes
    * Actualiza el unpaid_from_previous y unpaid_qty en los invoices siguientes del mismo proyecto
    * IMPORTANTE: NUNCA afecta al invoice actual, solo a los invoices posteriores
    * 
    * Ejemplo: Si se paga en Invoice 3, se actualizan Invoice 4 y 5, pero NUNCA el Invoice 3
    * 
    * @param Invoice $currentInvoice El invoice que se está pagando (este NO se afecta)
    * @param array $project_item_ids Los project_item_ids que se están pagando
    * @return void
    */
   private function ActualizarUnpaidFromPreviousEnInvoicesSiguientes($currentInvoice, $project_item_ids)
   {
      $project_id = $currentInvoice->getProject()->getProjectId();
      $current_invoice_id = $currentInvoice->getInvoiceId();
      $current_invoice_start_date = $currentInvoice->getStartDate();

      /** @var InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);

      // Obtener todos los invoices del proyecto ordenados por fecha de inicio
      $allInvoices = $invoiceRepo->ListarInvoicesRangoFecha('', $project_id, '', '', '');

      // Filtrar solo los invoices posteriores al actual (por fecha de inicio o ID)
      // IMPORTANTE: El invoice actual ($current_invoice_id) NUNCA se incluye aquí
      $followingInvoices = [];
      foreach ($allInvoices as $invoice) {
         /** @var Invoice $invoice */
         // Excluir explícitamente el invoice actual - NUNCA se afecta a sí mismo
         if ($invoice->getInvoiceId() != $current_invoice_id) {
            $invoiceDate = $invoice->getStartDate();
            // Considerar invoice siguiente si la fecha es mayor o igual (y es diferente)
            if (
               $invoiceDate > $current_invoice_start_date ||
               ($invoiceDate == $current_invoice_start_date && $invoice->getInvoiceId() > $current_invoice_id)
            ) {
               $followingInvoices[] = $invoice;
            }
         }
      }

      // Ordenar por fecha de inicio ascendente, luego por ID
      usort($followingInvoices, function ($a, $b) {
         /** @var Invoice $a */
         /** @var Invoice $b */
         $dateCompare = $a->getStartDate() <=> $b->getStartDate();
         if ($dateCompare != 0) {
            return $dateCompare;
         }
         return $a->getInvoiceId() <=> $b->getInvoiceId();
      });

      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);

      // Para cada project_item_id actualizado
      foreach ($project_item_ids as $project_item_id) {
         // Para cada invoice siguiente, recalcular unpaid_from_previous
         foreach ($followingInvoices as $followingInvoice) {
            /** @var Invoice $followingInvoice */
            $following_invoice_id = $followingInvoice->getInvoiceId();

            // Buscar el item en este invoice siguiente
            $following_item = $invoiceItemRepo->BuscarItem($following_invoice_id, $project_item_id);

            if ($following_item != null) {
               // Obtener todos los invoice items anteriores de este project_item
               $allInvoiceItems = $invoiceItemRepo->ListarInvoicesDeItem($project_item_id);

               // Calcular unpaid_from_previous: suma del unpaid_qty de todos los invoices anteriores
               $unpaid_from_previous = 0;

               foreach ($allInvoiceItems as $previousItem) {
                  /** @var InvoiceItem $previousItem */
                  $previousInvoice = $previousItem->getInvoice();
                  $previous_invoice_id = $previousInvoice->getInvoiceId();
                  $previous_invoice_date = $previousInvoice->getStartDate();
                  $following_invoice_date = $followingInvoice->getStartDate();

                  // Solo considerar invoices anteriores a este invoice siguiente
                  if (
                     $previous_invoice_date < $following_invoice_date ||
                     ($previous_invoice_date == $following_invoice_date && $previous_invoice_id < $following_invoice_id)
                  ) {

                     $quantity = $previousItem->getQuantity() ?? 0;
                     $paid_qty = $previousItem->getPaidQty() ?? 0;
                     $previous_unpaid_from_previous = $previousItem->getUnpaidFromPrevious() ?? 0;

                     // Calcular unpaid_qty: (quantity + unpaid_from_previous) - paid_qty
                     $unpaid_qty = ($quantity + $previous_unpaid_from_previous) - $paid_qty;

                     // Sumar al unpaid_from_previous acumulado
                     $unpaid_from_previous += max(0, $unpaid_qty);
                  }
               }

               // Actualizar unpaid_from_previous en el item siguiente
               $following_item->setUnpaidFromPrevious($unpaid_from_previous);

               // Recalcular unpaid_qty del invoice siguiente después de actualizar unpaid_from_previous
               $following_quantity = $following_item->getQuantity() ?? 0;
               $following_paid_qty = $following_item->getPaidQty() ?? 0;
               $following_price = $following_item->getPrice() ?? 0;

               // Calcular la cantidad total pagable de este invoice siguiente
               $following_total_payable = $following_quantity + $unpaid_from_previous;

               // Si paid_qty es mayor que lo pagable, ajustar paid_qty
               if ($following_paid_qty > $following_total_payable) {
                  $following_paid_qty = $following_total_payable;
                  $following_item->setPaidQty($following_paid_qty);
                  $following_item->setPaidAmount($following_paid_qty * $following_price);
               }

               // Calcular unpaid_qty: (quantity + unpaid_from_previous) - paid_qty
               $following_unpaid_qty = $following_total_payable - $following_paid_qty;

               // Asegurar que unpaid_qty no sea negativo
               $following_unpaid_qty = max(0, $following_unpaid_qty);

               $following_item->setUnpaidQty($following_unpaid_qty);
            }
         }
      }
   }

   /**
    * ListarInvoices: Listar los invoices
    *
    * @param int $start Inicio
    * @param int $limit Limite
    * @param string $sSearch Para buscar
    *
    * @author Marcel
    */
   public function ListarInvoices($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $company_id, $project_id, $fecha_inicial, $fecha_fin, $paid)
   {
      /** @var InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
      $resultado = $invoiceRepo->ListarInvoicesParaPaymentsConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $company_id, $project_id, $fecha_inicial, $fecha_fin, $paid);

      $data = [];

      foreach ($resultado['data'] as $value) {
         $invoice_id = $value->getInvoiceId();

         /** @var InvoiceItemRepository $invoiceItemRepo */
         $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
         // Usar TotalInvoiceFinalAmountThisPeriod para calcular el total (suma de Final Amount This Period)
         $total = $invoiceItemRepo->TotalInvoiceFinalAmountThisPeriod((string) $invoice_id);

         $data[] = array(
            "id" => $invoice_id,
            "number" => $value->getNumber(),
            "company" => $value->getProject()->getCompany()->getName(),
            "projectNumber" => $value->getProject()->getProjectNumber(),
            "project" => $value->getProject()->getDescription(),
            "project_id" => $value->getProject()->getProjectId(),
            "startDate" => $value->getStartDate()->format('m/d/Y'),
            "endDate" => $value->getEndDate()->format('m/d/Y'),
            "notes" => $this->truncate($value->getNotes(), 50),
            "total" => $total,
            "createdAt" => $value->getCreatedAt()->format('m/d/Y'),
            "paid" => $value->getPaid() ? 1 : 0
         );
      }

      return [
         'data' => $data,
         'total' => $resultado['total'], // ya viene con el filtro aplicado
      ];
   }

   /**
    * PaidInvoice: Paga un invoice
    * @param int $invoice_id Id
    * @author Marcel
    */
   public function PaidInvoice($invoice_id)
   {
      $resultado = array();
      $em = $this->getDoctrine()->getManager();

      $invoice = $this->getDoctrine()->getRepository(Invoice::class)
         ->find($invoice_id);
      /** @var Invoice $invoice */
      if (!is_null($invoice)) {

         // Verificar si ya está pagado - si está pagado, no hacer nada
         if ($invoice->getPaid()) {
            $resultado['success'] = false;
            $resultado['error'] = "This invoice is already paid";
            return $resultado;
         }

         // Marcar como pagado (no toggle, solo pagar)
         $invoice->setPaid(true);

         // Guardar los project_item_ids que se están actualizando para recalcular invoices siguientes
         $updated_project_item_ids = [];

         /** @var InvoiceItemRepository $invoiceItemRepo */
         $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
         $items = $invoiceItemRepo->ListarItems($invoice_id);

         foreach ($items as $item) {
            /** @var InvoiceItem $item */
            $quantity = $item->getQuantity();
            $unpaidFromPrevious = $item->getUnpaidFromPrevious();
            $quantityFromPrevious = $item->getQuantityFromPrevious();
            $price = $item->getPrice();
            $project_item_id = $item->getProjectItem()->getId();

            // Guardar project_item_id para actualizar invoices siguientes
            $updated_project_item_ids[] = $project_item_id;

            // Calcular cantidad pagable de este invoice (quantity + unpaid_from_previous)
            $quantityPayable = $quantity + $unpaidFromPrevious;

            // Calcular cantidad total completada (incluyendo anteriores)
            $quantityCompleted = $quantityPayable + $quantityFromPrevious;

            // Calcular montos pagados
            $paidQty = $quantityPayable;
            $paidAmount = $quantityPayable * $price;
            $paidAmountTotal = $quantityCompleted * $price;
            $unpaidQty = 0;

            // Actualizar item como pagado
            $item->setPaidQty($paidQty);
            $item->setPaidAmount($paidAmount);
            $item->setPaidAmountTotal($paidAmountTotal);
            $item->setUnpaidQty($unpaidQty);
         }

         // Actualizar unpaid_from_previous en invoices siguientes después de marcar como paid
         if (!empty($updated_project_item_ids)) {
            $this->ActualizarUnpaidFromPreviousEnInvoicesSiguientes($invoice, $updated_project_item_ids);
         }

         $em->flush();

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }
      return $resultado;
   }
}
