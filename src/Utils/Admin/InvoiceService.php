<?php

namespace App\Utils\Admin;

use App\Entity\Item;
use App\Entity\Project;
use App\Entity\Invoice;
use App\Entity\InvoiceItem;

use App\Entity\ProjectItem;
use App\Entity\SyncQueueQbwc;
use App\Repository\InvoiceItemRepository;
use App\Repository\InvoiceRepository;
use App\Repository\ProjectRepository;
use App\Utils\Base;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xls\Style\CellAlignment;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class InvoiceService extends Base
{

   /**
    * ValidarInvoice: Valida un invoice en la BD
    * @param string $invoice_id Id del invoice
    * @param string $project_id Id del proyecto
    * @param string $start_date Fecha inicial
    * @param string $end_date Fecha final 
    * @return string
    */
   public function ValidarInvoice($invoice_id, $project_id, $start_date, $end_date, $number)
   {
      $error = '';

      // verificar fechas
      /** @var InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
      $invoices = $invoiceRepo->ListarInvoicesRangoFecha('', (string) $project_id, (string) $start_date, (string) $end_date);

      if (!empty($invoices) && $invoices[0]->getInvoiceId() != $invoice_id) {
         $error = 'An invoice already exists for that date range';
      }

      // verificar que la fecha inicial no sea mayor que la inicial
      if ($start_date != '') {
         $start_date = \DateTime::createFromFormat('m/d/Y', $start_date);
      }

      if ($end_date != '') {
         $end_date = \DateTime::createFromFormat('m/d/Y', $end_date);
      }

      if ($start_date && $end_date) {
         if ($start_date > $end_date) {
            $error = "The start date cannot be greater than the end date.";
         }
      } else {
         $error = "Incorrect date format";
      }

      // verificar number
      if ($number !== '') {
         $invoice = $this->getDoctrine()->getRepository(Invoice::class)
            ->findOneBy(['number' => $number, 'project' => (string)$project_id]);
         if ($invoice != null && $invoice->getInvoiceId() != $invoice_id) {
            $error = "The invoice number is in use, please try entering another one.";
         }
      }

      return $error;
   }

   /**
    * ChangeNumber: Cambiar el number de un invoice
    * @param int $invoice_id Id
    * @author Marcel
    */
   public function ChangeNumber($invoice_id, $number)
   {
      $resultado = array();
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Invoice::class)
         ->find($invoice_id);
      /** @var Invoice $entity */
      if (!is_null($entity)) {

         $project_id = $entity->getProject()->getProjectId();

         // verificar number
         $invoice = $this->getDoctrine()->getRepository(Invoice::class)
            ->findOneBy(['number' => $number, 'project' => $project_id]);
         if ($invoice != null && $invoice->getInvoiceId() != $entity->getInvoiceId()) {
            $resultado['success'] = false;
            $resultado['error'] = "The invoice number is in use, please try entering another one.";
            return $resultado;
         }

         $entity->setNumber($number);

         $em->flush();

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }
      return $resultado;
   }


   /**
    * ExportarExcel: Exporta a excel el invoice
    *
    *
    * @author Marcel
    */
   public function ExportarExcel($invoice_id)
   {
      // Configurar excel
      Cell::setValueBinder(new AdvancedValueBinder());

      // === Estilo de bordes (4 lados) ===
      $styleArray = [
         'borders' => [
            'allBorders' => [
               'borderStyle' => Border::BORDER_THIN,
               'color' => ['argb' => 'FF000000'], // negro
            ],
         ],
      ];

      // Reader
      $reader = IOFactory::createReader('Xlsx');
      $objPHPExcel = $reader->load("bundles/metronic8/excel" . DIRECTORY_SEPARATOR . 'invoice.xlsx');
      $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);

      // Datos del invoice
      $invoice_entity = $this->getDoctrine()->getRepository(Invoice::class)->find($invoice_id);
      /** @var Invoice $invoice_entity */
      $project_entity = $invoice_entity->getProject();
      /** @var Project $project_entity */
      $project_id = $project_entity->getProjectId();

      // fecha actual
      $fecha_actual = date('m/d/Y');
      $objWorksheet->setCellValueExplicit("Q4", $fecha_actual, DataType::TYPE_STRING);

      $project_entity = $invoice_entity->getProject();
      $project_number = $project_entity->getProjectNumber();

      $number = $invoice_entity->getNumber();

      $objWorksheet->setCellValue("R4", $number);

      $start_date = $invoice_entity->getStartDate()->format('m/d/Y');
      $objWorksheet->setCellValueExplicit("Q6", $start_date, DataType::TYPE_STRING);

      $end_date = $invoice_entity->getEndDate()->format('m/d/Y');
      $objWorksheet->setCellValueExplicit("R6", $end_date, DataType::TYPE_STRING);

      // company
      $company_entity = $invoice_entity->getProject()->getCompany();
      $objWorksheet->setCellValue("G5", $company_entity->getName());
      $objWorksheet->setCellValue("G7", $company_entity->getPhone());
      $objWorksheet->setCellValue("H8", $company_entity->getContactName());
      $objWorksheet->setCellValue("H9", $company_entity->getContactEmail());

      // inspector
      $inspector_entity = $invoice_entity->getProject()->getInspector();
      if ($inspector_entity != null) {
         $objWorksheet->setCellValue("I5", $inspector_entity->getName());
         $objWorksheet->setCellValue("I7", $inspector_entity->getPhone());
         // $objWorksheet->setCellValue("D15", $inspector_entity->getEmail());
      }

      // project
      $county = $project_entity->getCountyObj() ? $project_entity->getCountyObj()->getDescription() : "";
      $objWorksheet->setCellValue("N3", $county);

      $objWorksheet->setCellValue("N4", $project_entity->getName());
      $objWorksheet->setCellValue("N6", $project_entity->getProjectIdNumber());
      $objWorksheet->setCellValue("N7", $project_entity->getSubcontract());
      $objWorksheet->setCellValue("N8", $project_number);

      // notes
      $objWorksheet->setCellValue("B11", $invoice_entity->getNotes());

      // Totales
      $total_contract_amount = 0;
      $total_amount_invoice_todate = 0;
      $total_unpaid = 0;
      $total_amount_from_previous = 0;
      $total_amount_final = 0;

      $fila_inicio = 16;
      $fila = $fila_inicio;

      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
      $items = $invoiceItemRepo->ListarItems($invoice_id);

      // Separar items regulares y change order
      $items_regulares = [];
      $items_change_order = [];

      foreach ($items as $value) {
         $change_order = $value->getProjectItem()->getChangeOrder();
         if ($change_order) {
            $change_order_date = $value->getProjectItem()->getChangeOrderDate();
            if ($change_order_date != null) {
               // Agrupar por mes/año (formato: "Y-m" para ordenar correctamente)
               $key_group = $change_order_date->format('Y-m');
               if (!isset($items_change_order[$key_group])) {
                  $items_change_order[$key_group] = [];
               }
               $items_change_order[$key_group][] = $value;
            } else {
               // Si no tiene fecha, agregarlo al grupo por defecto
               if (!isset($items_change_order['no-date'])) {
                  $items_change_order['no-date'] = [];
               }
               $items_change_order['no-date'][] = $value;
            }
         } else {
            $items_regulares[] = $value;
         }
      }

      // Ordenar los grupos de change order por fecha (más antiguo primero)
      ksort($items_change_order);

      $item_number = 1;

      // Escribir primero los items regulares
      foreach ($items_regulares as $value) {
         $project_item_id = $value->getProjectItem()->getId();

         $contract_qty = $value->getProjectItem()->getQuantity();
         $price = $value->getPrice();
         $contract_amount = $contract_qty * $price;
         $total_contract_amount += $contract_amount;

         $quantity_brought_forward = $value->getQuantityBroughtForward();
         $quantity = $value->getQuantity();

         $amount = $quantity * $price;
         $total_amount_invoice_todate += $amount;

         $quantity_from_previous = $value->getQuantityFromPrevious();

         $quantity_completed = $quantity + $quantity_from_previous;

         $amount_from_previous = $quantity_from_previous * $price;

         $total_amount_from_previous += $amount_from_previous;

         $amount_completed = $quantity_completed * $price;
         $total_amount_final += $amount_completed;

         $paid_qty = $value->getPaidQty();
         $unpaid_qty = $value->getUnpaidQty();
         $unpaid_amount = $unpaid_qty * $price;
         $total_unpaid += $unpaid_amount;

         // Escribir fila
         $unit = $value->getProjectItem()->getItem()->getUnit() != null ? $value->getProjectItem()->getItem()->getUnit()->getDescription() : '';
         $objWorksheet
            ->setCellValue('A' . $fila, $item_number)
            ->setCellValue('B' . $fila, $value->getProjectItem()->getItem()->getName())
            ->setCellValue('E' . $fila, $unit)
            ->setCellValue('F' . $fila, $price)
            ->setCellValue('G' . $fila, $contract_qty)
            ->setCellValue('H' . $fila, $contract_amount)
            ->setCellValue('I' . $fila, $quantity)
            ->setCellValue('J' . $fila, $amount)
            ->setCellValue('K' . $fila, $quantity_from_previous)
            ->setCellValue('L' . $fila, $amount_from_previous)
            ->setCellValue('N' . $fila, $quantity_completed)
            ->setCellValue('O' . $fila, $amount_completed)
            ->setCellValue('Q' . $fila, $unpaid_qty)
            ->setCellValue('R' . $fila, $unpaid_amount)
         ;

         // Aplicar bordes a toda la fila (A–P)
         $objWorksheet->getStyle("A{$fila}:L{$fila}")->applyFromArray($styleArray);
         $objWorksheet->getStyle("N{$fila}:O{$fila}")->applyFromArray($styleArray);
         $objWorksheet->getStyle("Q{$fila}:R{$fila}")->applyFromArray($styleArray);

         $item_number++;
         $fila++;
      }

      // Si hay items change order, agregar separación y escribirlos
      if (!empty($items_change_order)) {
         // Agregar fila en blanco para separar
         $fila++;

         // Escribir items change order agrupados por mes/año
         foreach ($items_change_order as $group_key => $group_items) {
            // Obtener mes y año para el encabezado
            $month_name = '';
            $year = '';
            if ($group_key !== 'no-date' && !empty($group_items)) {
               $first_item_date = $group_items[0]->getProjectItem()->getChangeOrderDate();
               if ($first_item_date != null) {
                  // Nombres de meses en inglés
                  $months = [
                     'January',
                     'February',
                     'March',
                     'April',
                     'May',
                     'June',
                     'July',
                     'August',
                     'September',
                     'October',
                     'November',
                     'December'
                  ];
                  $month_name = $months[(int)$first_item_date->format('n') - 1];
                  $year = $first_item_date->format('Y');
               }
            }

            // Escribir encabezado del grupo (solo si tiene fecha válida)
            if ($month_name && $year) {
               $objWorksheet
                  ->setCellValue('B' . $fila, "Change Order in {$month_name} {$year}");
               $objWorksheet->getStyle("B{$fila}")->getFont()->setBold(true);
               $fila++;
            }

            // Escribir items del grupo
            foreach ($group_items as $value) {
               $project_item_id = $value->getProjectItem()->getId();

               $contract_qty = $value->getProjectItem()->getQuantity();
               $price = $value->getPrice();
               $contract_amount = $contract_qty * $price;
               $total_contract_amount += $contract_amount;

               $quantity_brought_forward = $value->getQuantityBroughtForward();
               $quantity = $value->getQuantity();

               $amount = $quantity * $price;
               $total_amount_invoice_todate += $amount;

               $quantity_from_previous = $value->getQuantityFromPrevious();

               $quantity_completed = $quantity + $quantity_from_previous;

               $amount_from_previous = $quantity_from_previous * $price;

               $total_amount_from_previous += $amount_from_previous;

               $amount_completed = $quantity_completed * $price;
               $total_amount_final += $amount_completed;

               $paid_qty = $value->getPaidQty();
               $unpaid_qty = $value->getUnpaidQty();
               $unpaid_amount = $unpaid_qty * $price;
               $total_unpaid += $unpaid_amount;

               // Escribir fila
               $unit = $value->getProjectItem()->getItem()->getUnit() != null ? $value->getProjectItem()->getItem()->getUnit()->getDescription() : '';
               $objWorksheet
                  ->setCellValue('A' . $fila, $item_number)
                  ->setCellValue('B' . $fila, $value->getProjectItem()->getItem()->getName())
                  ->setCellValue('E' . $fila, $unit)
                  ->setCellValue('F' . $fila, $price)
                  ->setCellValue('G' . $fila, $contract_qty)
                  ->setCellValue('H' . $fila, $contract_amount)
                  ->setCellValue('I' . $fila, $quantity)
                  ->setCellValue('J' . $fila, $amount)
                  ->setCellValue('K' . $fila, $quantity_from_previous)
                  ->setCellValue('L' . $fila, $amount_from_previous)
                  ->setCellValue('N' . $fila, $quantity_completed)
                  ->setCellValue('O' . $fila, $amount_completed)
                  ->setCellValue('Q' . $fila, $unpaid_qty)
                  ->setCellValue('R' . $fila, $unpaid_amount)
               ;

               // Aplicar bordes a toda la fila (A–P)
               $objWorksheet->getStyle("A{$fila}:L{$fila}")->applyFromArray($styleArray);
               $objWorksheet->getStyle("N{$fila}:O{$fila}")->applyFromArray($styleArray);
               $objWorksheet->getStyle("Q{$fila}:R{$fila}")->applyFromArray($styleArray);

               $item_number++;
               $fila++;
            }
         }
      }

      // Totales
      $objWorksheet->setCellValue("G$fila", "TOTAL CONTRACT AMOUNT:")->getStyle("G$fila")->getFont()->setBold(true);
      $objWorksheet->setCellValue("H$fila", $total_contract_amount)->getStyle("H$fila")->getFont()->setBold(true);

      $objWorksheet->setCellValue("I$fila", "TOTAL AMOUNT (BTD):")->getStyle("I$fila")->getFont()->setBold(true);
      $objWorksheet->setCellValue("J$fila", $total_amount_invoice_todate)->getStyle("I$fila")->getFont()->setBold(true);

      $objWorksheet->setCellValue("K$fila", "TOTAL AMOUNT (PREVIOUS BILL):")->getStyle("K$fila")->getFont()->setBold(true);
      $objWorksheet->setCellValue("L$fila", $total_amount_from_previous)->getStyle("L$fila")->getFont()->setBold(true);

      $objWorksheet->setCellValue("N$fila", "TOTAL BILLED AMOUNT (THIS PERIOD):")->getStyle("N$fila")->getFont()->setBold(true);
      $objWorksheet->setCellValue("O$fila", $total_amount_final)->getStyle("O$fila")->getFont()->setBold(true);

      $objWorksheet->setCellValue("Q$fila", "TOTAL PENDING BALANCE (BTD):")->getStyle("Q$fila")->getFont()->setBold(true);
      $objWorksheet->setCellValue("R$fila", $total_unpaid)->getStyle("R$fila")->getFont()->setBold(true);

      // Bordes de la fila total
      $objWorksheet->getStyle("G{$fila}:L{$fila}")->applyFromArray($styleArray);
      $objWorksheet->getStyle("N{$fila}:O{$fila}")->applyFromArray($styleArray);
      $objWorksheet->getStyle("Q{$fila}:R{$fila}")->applyFromArray($styleArray);

      /* ===========================================
         * COLORES DE FONDO (desde fila 6 hasta totales)
         * =========================================== */
      $lastRow = $fila;

      //G-H (azul claro)
      // $objWorksheet->getStyle("G{$fila_inicio}:H{$lastRow}")
      //    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
      //    ->getStartColor()->setARGB('FFDAEEF3');

      // I-J (rojo claro)
      // $objWorksheet->getStyle("I{$fila_inicio}:J{$lastRow}")
      //    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
      //    ->getStartColor()->setARGB('FFF79494');

      // K-L (naranja suave)
      // $objWorksheet->getStyle("K{$fila_inicio}:L{$lastRow}")
      //    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
      //    ->getStartColor()->setARGB('FFFCD5B4');


      // N-O (verde claro)
      // $objWorksheet->getStyle("N{$fila_inicio}:O{$lastRow}")
      //    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
      //    ->getStartColor()->setARGB('FFD8E4BC');

      // Q-R (amarillo claro)
      // $objWorksheet->getStyle("Q{$fila_inicio}:R{$lastRow}")
      //    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
      //    ->getStartColor()->setARGB('FFF2D068');


      // Nuevos campos bajo total
      $fila = $fila + 2;
      $fila_retainage_inicio = $fila;

      $porciento_retainage = $this->CalcularPorcientoRetainage($project_entity, $total_amount_final);
      $porciento_retainage = $porciento_retainage / 100;

      // LESS RETAINAGE $
      $objWorksheet->setCellValue("N$fila", "LESS RETAINAGE $")->getStyle("N$fila")->getFont()->setBold(true);

      // aplicar 10 % al $total_amount_fina
      $total_amount_final_10 = $total_amount_final * $porciento_retainage;
      $objWorksheet->setCellValue("O$fila", $total_amount_final_10)->getStyle("O$fila")->getFont()->setBold(true);

      $fila = $fila + 1;

      // AMOUNT EARNED LESS RETAINAGE $
      $objWorksheet->setCellValue("N$fila", "AMOUNT EARNED LESS RETAINAGE $")->getStyle("N$fila")->getFont()->setBold(true);
      $amount_earned_less_retainage = $total_amount_final - $total_amount_final_10;
      $objWorksheet->setCellValue("O$fila", $amount_earned_less_retainage)->getStyle("O$fila")->getFont()->setBold(true);

      $fila = $fila + 1;

      // LESS PREVIOUS APPLICATIONS $
      $objWorksheet->setCellValue("N$fila", "LESS PREVIOUS APPLICATIONS $")->getStyle("N$fila")->getFont()->setBold(true);
      $less_previous_applications = 0;
      $objWorksheet->setCellValue("O$fila", $less_previous_applications)->getStyle("O$fila")->getFont()->setBold(true);

      $fila = $fila + 1;

      // CURRENT AMOUNT DUE $
      $objWorksheet->setCellValue("N$fila", "CURRENT AMOUNT DUE $")->getStyle("N$fila")->getFont()->setBold(true);
      $current_amount_due = $amount_earned_less_retainage - $less_previous_applications;
      $objWorksheet->setCellValue("O$fila", $current_amount_due)->getStyle("O$fila")->getFont()->setBold(true);

      $fila = $fila + 1;

      // CURRENT RETAINAGE $
      $objWorksheet->setCellValue("N$fila", "CURRENT RETAINAGE $")->getStyle("N$fila")->getFont()->setBold(true);
      $current_retainage = $this->CalcularCurrentRetainage($project_id, $porciento_retainage);
      $objWorksheet->setCellValue("O$fila", $current_retainage)->getStyle("O$fila")->getFont()->setBold(true);

      // aplicar borde
      $objWorksheet->getStyle("N{$fila_retainage_inicio}:O{$fila}")->applyFromArray($styleArray);

      // Color de fondo naranja para la sección final (N–O)
      // $objWorksheet->getStyle("N{$fila_retainage_inicio}:O{$fila}")
      //    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
      //    ->getStartColor()->setARGB('FFFCD5B4');


      // Guardar Excel
      $fichero = "invoice-$number.xlsx";
      $objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
      $objWriter->save("uploads" . DIRECTORY_SEPARATOR . "invoice" . DIRECTORY_SEPARATOR . $fichero);

      $objPHPExcel->disconnectWorksheets();
      unset($objPHPExcel);

      $ruta = $this->ObtenerURL();
      $url = $ruta . 'uploads/invoice/' . $fichero;

      return $url;
   }

   /**
    * Summary of CalcularPorcientoRetainage
    * @param Project $project_entity
    * @param float $total_amount_final
    * @return float
    */
   private function CalcularPorcientoRetainage($project_entity, $total_amount_final)
   {
      $porciento = 0;

      if ($project_entity->getRetainage()) {
         $porciento = $project_entity->getRetainagePercentage();
         $porciento_adjustment_percentage = $project_entity->getRetainageAdjustmentPercentage();
         $porciento_adjustment_completion = $project_entity->getRetainageAdjustmentCompletion();
         $contract_amount = $project_entity->getContractAmount();

         // revisar el total_amount_final sobre pasa al contract_amount en el porciento_adjustment_completion configurado
         // si eso pasa entonces el porciento = porciento_adjustment_percentage
         if ($total_amount_final > $contract_amount * ($porciento_adjustment_completion / 100)) {
            $porciento = $porciento_adjustment_percentage;
         }
      }


      return $porciento;
   }

   private function CalcularCurrentRetainage($project_id, $porciento_retainage)
   {
      if (empty($project_id) || empty($porciento_retainage)) {
         return 0;
      }

      /** @var InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);

      $invoices = $invoiceRepo->ListarInvoicesDeProject($project_id);
      if (empty($invoices)) {
         return 0;
      }

      $total_retainage = 0;
      foreach ($invoices as $invoice) {
         $invoice_total = $invoiceItemRepo->TotalInvoiceBroughtForward((string) $invoice->getInvoiceId());
         if ($invoice_total > 0) {
            $total_retainage += $invoice_total * $porciento_retainage;
         }
      }

      return $total_retainage;
   }



   /**
    * EliminarItem: Elimina un item en la BD
    * @param int $invoice_item_id Id
    * @author Marcel
    */
   public function EliminarItem($invoice_item_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(InvoiceItem::class)
         ->find($invoice_item_id);
      /**@var InvoiceItem $entity */
      if ($entity != null) {

         $item_name = $entity->getProjectItem()->getItem()->getName();

         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Item Invoice";
         $log_descripcion = "The item details invoice is deleted: $item_name";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * CargarDatosInvoice: Carga los datos de un invoice
    *
    * @param int $invoice_id Id
    *
    * @author Marcel
    */
   public function CargarDatosInvoice($invoice_id)
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

         $resultado['success'] = true;
         $resultado['invoice'] = $arreglo_resultado;
      }

      return $resultado;
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

         $quantity_brought_forward = $value->getQuantityBroughtForward();
         $quantity_completed = $quantity + $quantity_from_previous;

         $amount = $quantity * $price;

         $total_amount = $quantity_completed * $price;

         $amount_from_previous = $quantity_from_previous * $price;

         $amount_completed = $quantity_completed * $price;

         $paid_qty = $value->getPaidQty();

         $unpaid_qty = $value->getUnpaidQty();
         $unpaid_amount = $unpaid_qty * $price;


         $quantity_final = $quantity + $quantity_brought_forward;
         $amount_final = $quantity_brought_forward * $price;

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
            "amount_from_previous" => $amount_from_previous,
            "amount_completed" => $amount_completed,
            "paid_qty" => $paid_qty,
            "unpaid_qty" => $unpaid_qty,
            "unpaid_amount" => $unpaid_amount,
            "quantity_brought_forward" => $quantity_brought_forward,
            "quantity_final" => $quantity_final,
            "amount_final" => $amount_final,
            "principal" => $value->getProjectItem()->getPrincipal(),
            "change_order" => $value->getProjectItem()->getChangeOrder(),
            "change_order_date" => $value->getProjectItem()->getChangeOrderDate() != null ? $value->getProjectItem()->getChangeOrderDate()->format('m/d/Y') : '',
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
    * EliminarInvoice: Elimina un rol en la BD
    * @param int $invoice_id Id
    * @author Marcel
    */
   public function EliminarInvoice($invoice_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Invoice::class)
         ->find($invoice_id);
      /**@var Invoice $entity */
      if ($entity != null) {

         // eliminar informacion
         $this->EliminarInformacionDeInvoice($invoice_id);

         $number = $entity->getNumber();

         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Invoice";
         $log_descripcion = "The invoice #$number is deleted";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * EliminarInvoices: Elimina los invoices seleccionados en la BD
    * @param int $ids Ids
    * @author Marcel
    */
   public function EliminarInvoices($ids)
   {
      $em = $this->getDoctrine()->getManager();

      if ($ids != "") {
         $ids = explode(',', $ids);
         $cant_eliminada = 0;
         $cant_total = 0;
         foreach ($ids as $invoice_id) {
            if ($invoice_id != "") {
               $cant_total++;
               $entity = $this->getDoctrine()->getRepository(Invoice::class)
                  ->find($invoice_id);
               /**@var Invoice $entity */
               if ($entity != null) {

                  // eliminar informacion
                  $this->EliminarInformacionDeInvoice($invoice_id);

                  $number = $entity->getNumber();

                  $em->remove($entity);
                  $cant_eliminada++;

                  //Salvar log
                  $log_operacion = "Delete";
                  $log_categoria = "Invoice";
                  $log_descripcion = "The invoice #$number is deleted";
                  $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
               }
            }
         }
      }
      $em->flush();

      if ($cant_eliminada == 0) {
         $resultado['success'] = false;
         $resultado['error'] = "The invoices could not be deleted, because they are associated with a invoice";
      } else {
         $resultado['success'] = true;

         $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected invoices because they are associated with a invoice";
         $resultado['message'] = $mensaje;
      }

      return $resultado;
   }

   /**
    * ActualizarInvoice: Actuializa los datos del rol en la BD
    * @param int $invoice_id Id
    * @author Marcel
    */
   public function ActualizarInvoice($invoice_id, $number, $project_id, $start_date, $end_date, $notes, $paid, $items, $exportar)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Invoice::class)
         ->find($invoice_id);
      /** @var Invoice $entity */
      if ($entity != null) {

         // verificar fechas
         /** @var InvoiceRepository $invoiceRepo */
         $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
         $invoices = $invoiceRepo->ListarInvoicesRangoFecha('', $project_id, $start_date, $end_date);
         if (!empty($invoices) && $invoices[0]->getInvoiceId() != $entity->getInvoiceId()) {
            $resultado['success'] = false;
            $resultado['error'] = "An invoice already exists for that date range";
            return $resultado;
         }

         // verificar que la fecha inicial no sea mayor que la inicial
         if ($start_date != '') {
            $start_date = \DateTime::createFromFormat('m/d/Y', $start_date);
         }

         if ($end_date != '') {
            $end_date = \DateTime::createFromFormat('m/d/Y', $end_date);
         }

         if ($start_date && $end_date) {
            if ($start_date > $end_date) {
               $resultado['success'] = false;
               $resultado['error'] = "The start date cannot be greater than the end date.";
               return $resultado;
            }
         } else {
            $resultado['success'] = false;
            $resultado['error'] = "Incorrect date format";
            return $resultado;
         }

         // verificar number
         $invoice = $this->getDoctrine()->getRepository(Invoice::class)
            ->findOneBy(['number' => $number, 'project' => $project_id]);
         if ($invoice != null && $invoice->getInvoiceId() != $entity->getInvoiceId()) {
            $resultado['success'] = false;
            $resultado['error'] = "The invoice number is in use, please try entering another one.";
            return $resultado;
         }

         $entity->setNumber($number);

         $entity->setStartDate($start_date);
         $entity->setEndDate($end_date);

         $entity->setNotes($notes);
         $entity->setPaid($paid);

         if ($project_id != '') {
            $project = $this->getDoctrine()->getRepository(Project::class)
               ->find($project_id);
            $entity->setProject($project);
         }

         $entity->setUpdatedAt(new \DateTime());

         // items
         $this->SalvarItems($entity, $items);

         // salvar en la cola
         $this->SalvarInvoiceQuickbook($entity);

         $em->flush();

         //Salvar log
         $log_operacion = "Update";
         $log_categoria = "Invoice";

         $number = $entity->getNumber();
         $log_descripcion = "The invoice #$number is modified";

         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;

         // exportar
         $url = '';
         if ($exportar == 1) {
            $url = $this->ExportarExcel($invoice_id);
         }
         $resultado['url'] = $url;

         return $resultado;
      }
   }

   /**
    * SalvarInvoice: Guarda los datos de invoice en la BD
    * @param string $description Nombre
    * @author Marcel
    */
   public function SalvarInvoice($number, $project_id, $start_date, $end_date, $notes, $paid, $items, $exportar)
   {
      $em = $this->getDoctrine()->getManager();

      // verificar fechas
      /** @var InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
      $invoices = $invoiceRepo->ListarInvoicesRangoFecha('', $project_id, $start_date, $end_date);
      if (!empty($invoices)) {
         $resultado['success'] = false;
         $resultado['error'] = "An invoice already exists for that date range";
         return $resultado;
      }

      // verificar que la fecha inicial no sea mayor que la inicial
      if ($start_date != '') {
         $start_date = \DateTime::createFromFormat('m/d/Y', $start_date);
      }

      if ($end_date != '') {
         $end_date = \DateTime::createFromFormat('m/d/Y', $end_date);
      }

      if ($start_date && $end_date) {
         if ($start_date > $end_date) {
            $resultado['success'] = false;
            $resultado['error'] = "The start date cannot be greater than the end date.";
            return $resultado;
         }
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "Incorrect date format";
         return $resultado;
      }

      // verificar number
      if ($number !== '') {
         $invoice = $this->getDoctrine()->getRepository(Invoice::class)
            ->findOneBy(['number' => $number, 'project' => $project_id]);
         if ($invoice != null) {
            $resultado['success'] = false;
            $resultado['error'] = "The invoice number is in use, please try entering another one.";
            return $resultado;
         }
      } else {
         // number
         /** @var InvoiceRepository $invoiceRepo */
         $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
         $invoices = $invoiceRepo->ListarInvoicesDeProject($project_id);
         $number = 1;
         if (!empty($invoices)) {
            $number = intval($invoices[0]->getNumber()) + 1;
         }
      }

      $entity = new Invoice();

      $entity->setNumber($number);

      $entity->setStartDate($start_date);
      $entity->setEndDate($end_date);

      $entity->setNotes($notes);
      $entity->setPaid($paid);

      if ($project_id != '') {
         $project = $this->getDoctrine()->getRepository(Project::class)
            ->find($project_id);
         $entity->setProject($project);
      }

      $entity->setCreatedAt(new \DateTime());

      $em->persist($entity);

      // items
      $this->SalvarItems($entity, $items);

      $em->flush();

      // salvar en la cola
      $this->SalvarInvoiceQuickbook($entity);

      $em->flush();

      //Salvar log
      $log_operacion = "Add";
      $log_categoria = "Invoice";
      $log_descripcion = "The invoice #$number is added";
      $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

      $resultado['success'] = true;

      // exportar
      $url = '';
      if ($exportar == 1) {
         $invoice_id = $entity->getInvoiceId();
         $url = $this->ExportarExcel($invoice_id);
      }
      $resultado['url'] = $url;

      return $resultado;
   }

   /**
    * SalvarInvoiceQuickbook
    * @param Invoice $entity
    * @return void
    */
   public function SalvarInvoiceQuickbook($entity)
   {
      $em = $this->getDoctrine()->getManager();

      $invoice_id = $entity->getInvoiceId();

      $sync_queue_qbwc = $this->getDoctrine()->getRepository(SyncQueueQbwc::class)
         ->findOneBy(['tipo' => 'invoice', 'entidadId' => $invoice_id]);
      $is_new_sync_queue_qbwc = false;
      if ($sync_queue_qbwc == null) {
         $sync_queue_qbwc = new SyncQueueQbwc();
         $is_new_sync_queue_qbwc = true;
      }

      $sync_queue_qbwc->setEstado('pendiente');

      if ($is_new_sync_queue_qbwc) {
         $sync_queue_qbwc->setTipo('invoice');
         $sync_queue_qbwc->setEntidadId($invoice_id);
         $sync_queue_qbwc->setIntentos(0);

         $sync_queue_qbwc->setCreatedAt(new \DateTime());

         $em->persist($sync_queue_qbwc);
      }
   }

   /**
    * SalvarItems
    * @param array $items
    * @param Invoice $entity
    * @return void
    */
   public function SalvarItems($entity, $items)
   {
      $em = $this->getDoctrine()->getManager();

      //items

      foreach ($items as $value) {

         $invoice_item_entity = null;

         if (is_numeric($value->invoice_item_id)) {
            $invoice_item_entity = $this->getDoctrine()->getRepository(InvoiceItem::class)
               ->find($value->invoice_item_id);
         }

         $is_new_item = false;
         if ($invoice_item_entity == null) {
            $invoice_item_entity = new InvoiceItem();
            $is_new_item = true;
         }

         $invoice_item_entity->setQuantityFromPrevious($value->quantity_from_previous);
         $invoice_item_entity->setUnpaidFromPrevious($value->unpaid_from_previous);
         $invoice_item_entity->setUnpaidQty($value->unpaid_qty);
         $invoice_item_entity->setQuantity($value->quantity);
         $invoice_item_entity->setPrice($value->price);
         $invoice_item_entity->setQuantityBroughtForward($value->quantity_brought_forward);

         if ($value->project_item_id != '') {
            $project_item_entity = $this->getDoctrine()->getRepository(ProjectItem::class)->find($value->project_item_id);
            $invoice_item_entity->setProjectItem($project_item_entity);
         }


         if ($is_new_item) {
            $invoice_item_entity->setInvoice($entity);

            $em->persist($invoice_item_entity);
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
   public function ListarInvoices($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $company_id, $project_id, $fecha_inicial, $fecha_fin)
   {
      /** @var InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
      $resultado = $invoiceRepo->ListarInvoicesConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $company_id, $project_id, $fecha_inicial, $fecha_fin);

      $data = [];

      foreach ($resultado['data'] as $value) {
         $invoice_id = $value->getInvoiceId();

         /** @var InvoiceItemRepository $invoiceItemRepo */
         $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
         $total = $invoiceItemRepo->TotalInvoice($invoice_id);

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
}
