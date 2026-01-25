<?php

namespace App\Utils\Admin;

use App\Entity\Item;
use App\Entity\Project;
use App\Entity\Invoice;
use App\Entity\InvoiceItem;

use App\Entity\ProjectItem;
use App\Entity\ProjectItemHistory;
use App\Entity\SyncQueueQbwc;
use App\Repository\InvoiceItemRepository;
use App\Repository\InvoiceRepository;
use App\Repository\ProjectItemHistoryRepository;
use App\Repository\ProjectItemRepository;
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
    * Orden determinístico de invoices (fecha inicio, luego ID).
    *
    * @param Invoice[] $invoices
    */
   private function sortInvoicesByStartDateAndId(array &$invoices): void
   {
      usort($invoices, function ($a, $b) {
         /** @var Invoice $a */
         /** @var Invoice $b */
         $dateCompare = $a->getStartDate() <=> $b->getStartDate();
         if ($dateCompare !== 0) {
            return $dateCompare;
         }
         return $a->getInvoiceId() <=> $b->getInvoiceId();
      });
   }

   /**
    * Construye series (quantity/paid/qbf) y prefijos para un project_item
    * a lo largo de la lista ordenada de invoices del proyecto.
    *
    * NOTA: las sumas "previas" para un índice i se leen como prefix[i]
    * (porque prefix[0]=0 y prefix[i]=SUM(0..i-1)).
    *
    * @param Invoice[] $allInvoices
    * @param array<int, InvoiceItem> $invoiceItemMap invoice_id => InvoiceItem
    * @return array{
    *   qbf: float[],
    *   prefixQty: float[],
    *   prefixPaid: float[],
    *   prefixQbf: float[]
    * }
    */
   private function buildInvoiceItemSeriesForInvoices(array $allInvoices, array $invoiceItemMap, ?int $overrideInvoiceId = null, ?float $overrideQbf = null): array
   {
      $qbf = [];
      $prefixQty = [0.0];
      $prefixPaid = [0.0];
      $prefixQbf = [0.0];

      foreach ($allInvoices as $idx => $invoice) {
         /** @var Invoice $invoice */
         $invoiceId = (int) $invoice->getInvoiceId();
         $item = $invoiceItemMap[$invoiceId] ?? null;

         $qty = (float) (($item?->getQuantity()) ?? 0);
         $paid = (float) (($item?->getPaidQty()) ?? 0);
         $qbfValue = (float) (($item?->getQuantityBroughtForward()) ?? 0);

         if ($overrideInvoiceId !== null && $invoiceId === (int) $overrideInvoiceId) {
            $qbfValue = (float) ($overrideQbf ?? 0);
         }

         $qbf[$idx] = $qbfValue;

         $prefixQty[$idx + 1] = $prefixQty[$idx] + $qty;
         $prefixPaid[$idx + 1] = $prefixPaid[$idx] + $paid;
         $prefixQbf[$idx + 1] = $prefixQbf[$idx] + $qbfValue;
      }

      return [
         'qbf' => $qbf,
         'prefixQty' => $prefixQty,
         'prefixPaid' => $prefixPaid,
         'prefixQbf' => $prefixQbf,
      ];
   }

   /**
    * Calcula unpaid_qty (columna roja) para INVOICES, usando las reglas actuales:
    * - El QBF NO se arrastra a invoices siguientes: solo afecta al invoice actual.
    * - Regla 1 (sin pagos previos): unpaid = SUM(quantity prev) - QBF(actual)
    * - Regla 2 (con pagos previos):
    *   - baseDebtPrev = max(0, SUM(quantity prev) - SUM(paid_qty prev))
    *   - si SUM(paid_qty prev) > SUM(qbf prev) => QBF desactivado => unpaid=baseDebtPrev
    *   - si no => QBF activo => unpaid=max(0, baseDebtPrev - QBF(actual))
    */
   /**
    * 
    */
   /**
    * Nueva Fórmula Simplificada:
    * Unpaid = (Suma Qty Anteriores - Suma Paid Anteriores) - QBF Actual
    */
   private function calculateInvoiceUnpaidQty(float $sumPrevQty, float $sumPrevPaid, float $currentQbf): float
   {
      // (100 del inv1) - (70 del inv1) = 30.
      // 30 - 0 (QBF) = 30.
      $deudaNetaPrev = $sumPrevQty - $sumPrevPaid;

      return max(0, $deudaNetaPrev - $currentQbf);
   }

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
    * @author Marcel
    */
   public function ExportarExcel($invoice_id)
   {
      $em = $this->getDoctrine()->getManager();
      $invoiceItemRepo = $em->getRepository(InvoiceItem::class);
      $invoiceRepo = $em->getRepository(Invoice::class);

      // 1. OBTENER DATOS
      $invoice_entity = $invoiceRepo->find($invoice_id);
      if (!$invoice_entity) return null;

      // ---  LOS DATOS CALCULADOS DE LA WEB ---
      $datos_web = $this->ListarItemsDeInvoice($invoice_id);
      $mapa_datos_web = [];
      foreach ($datos_web as $dato) {
         // Unpaid actual como el Previous
         $mapa_datos_web[$dato['invoice_item_id']] = [
            'unpaid_qty' => $dato['unpaid_qty'],
            'unpaid_from_previous' => $dato['unpaid_from_previous'] // <--- ESTO ES NUEVO
         ];
      }
      //dd($mapa_datos_web);
      // ---------------------------------------------------------------

      $project_entity = $invoice_entity->getProject();
      $project_id = $project_entity->getProjectId();
      $currentInvoiceId = $invoice_id;

      $allInvoicesHistory = $invoiceRepo->ListarInvoicesRangoFecha('', $project_id, '', '', '');
      $this->sortInvoicesByStartDateAndId($allInvoicesHistory);

      // 2. SEPARAR ITEMS
      $items = $invoiceItemRepo->ListarItems($invoice_id);
      $items_regulares = [];
      $items_change_order = [];

      foreach ($items as $value) {
         $change_order = $value->getProjectItem()->getChangeOrder();
         if ($change_order) {
            $date = $value->getProjectItem()->getChangeOrderDate();
            $key = ($date) ? $date->format('Y-m') : 'no-date';
            $items_change_order[$key][] = $value;
         } else {
            $items_regulares[] = $value;
         }
      }
      ksort($items_change_order);

      // 3. CARGAR EXCEL Y DEFINIR ESTILOS
      Cell::setValueBinder(new AdvancedValueBinder());

      $styleLeft = [
         'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
         'font' => ['name' => 'Arial', 'size' => 10, 'bold' => false],
         'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
      ];
      $styleRight = [
         'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
         'font' => ['name' => 'Arial', 'size' => 10, 'bold' => false],
         'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER]
      ];

      $reader = IOFactory::createReader('Xlsx');
      $objPHPExcel = $reader->load("bundles/metronic8/excel" . DIRECTORY_SEPARATOR . 'invoice.xlsx');
      $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);

      // ======================================================================
      // CALCULO DE ESPACIO
      // ======================================================================
      $start_row_data = 16;
      $fila_footer_inicio = 0;
      for ($i = $start_row_data; $i < 200; $i++) {
         $valE = strtoupper((string)$objWorksheet->getCell('E' . $i)->getValue());
         $valG = strtoupper((string)$objWorksheet->getCell('G' . $i)->getValue());
         if (strpos($valG, 'CONTRACT AMOUNT') !== false || strpos($valE, 'TOTAL') !== false) {
            $fila_footer_inicio = $i;
            break;
         }
      }
      if ($fila_footer_inicio == 0) $fila_footer_inicio = 41;

      // --- CÁLCULO EXACTO DE FILAS ---
      $filas_necesarias = count($items_regulares);

      if (!empty($items_change_order)) {
         $esPrimerGrupoCalculo = true;

         foreach ($items_change_order as $group_key => $group) {
            $filas_necesarias += count($group);

            if ($group_key !== 'no-date') {
               if ($esPrimerGrupoCalculo) {
                  $filas_necesarias += 2; // 1 Fila Blanca + 1 Fila Título
                  $esPrimerGrupoCalculo = false;
               } else {
                  $filas_necesarias += 1; // Solo 1 Fila Título
               }
            }
         }
      }

      $filas_disponibles = $fila_footer_inicio - $start_row_data;

      if ($filas_necesarias > $filas_disponibles) {
         $a_insertar = $filas_necesarias - $filas_disponibles;
         $objWorksheet->insertNewRowBefore($fila_footer_inicio, $a_insertar);
         $fila_footer_inicio += $a_insertar;
      } elseif ($filas_necesarias < $filas_disponibles) {
         $a_borrar = $filas_disponibles - $filas_necesarias;
         if ($a_borrar > 0) {
            $objWorksheet->removeRow($start_row_data + $filas_necesarias, $a_borrar);
            $fila_footer_inicio -= $a_borrar;
         }
      }
      $fila_retainage = $fila_footer_inicio + 1;

      // 4. DATOS CABECERA
      $objWorksheet->setCellValueExplicit("R4", date('m/d/Y'), DataType::TYPE_STRING);
      $objWorksheet->setCellValue("S4", $invoice_entity->getNumber());
      $objWorksheet->setCellValueExplicit("R6", $invoice_entity->getStartDate()->format('m/d/Y'), DataType::TYPE_STRING);
      $objWorksheet->setCellValueExplicit("S6", $invoice_entity->getEndDate()->format('m/d/Y'), DataType::TYPE_STRING);

      $company = $project_entity->getCompany();
      $objWorksheet->setCellValue("G5", $company->getName());
      $objWorksheet->setCellValue("G7", $company->getPhone());
      $objWorksheet->setCellValue("H8", $company->getContactName());
      $objWorksheet->mergeCells("H8:J8");
      $objWorksheet->setCellValue("H9", $company->getContactEmail());
      $objWorksheet->mergeCells("H9:J9");

      if ($insp = $project_entity->getInspector()) {
         $objWorksheet->setCellValue("I5", $insp->getName());
         $objWorksheet->getStyle("I5")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE);
         $objWorksheet->setCellValue("I7", $insp->getPhone());
      }

      $objWorksheet->setCellValue("N3", $this->getCountiesDescriptionForProject($project_entity));
      $objWorksheet->mergeCells("N3:P3");
      $objWorksheet->setCellValue("N4", $project_entity->getName());
      $objWorksheet->mergeCells("N4:P4");
      $objWorksheet->setCellValue("N6", $project_entity->getProjectIdNumber());
      $objWorksheet->mergeCells("N6:P6");
      $objWorksheet->setCellValue("N7", $project_entity->getSubcontract());
      $objWorksheet->mergeCells("N7:P7");
      $objWorksheet->setCellValue("N8", $project_entity->getProjectNumber());
      $objWorksheet->mergeCells("N8:P8");
      $objWorksheet->setCellValue("B11", $invoice_entity->getNotes());

      // VARIABLES Y FORMATOS
      $item_number = 1;
      $fila = $start_row_data;
      $currencyFormat = '"$"#,##0.00';
      $qtyFormat = '#,##0.00';

      $aplicarFormatoFila = function ($sheet, $f) use ($styleLeft, $styleRight, $currencyFormat, $qtyFormat) {
         $sheet->getStyle("A{$f}:E{$f}")->applyFromArray($styleLeft);
         $sheet->getStyle("F{$f}:S{$f}")->applyFromArray($styleRight);
         $sheet->getRowDimension($f)->setRowHeight(18);
         $colsMoney = ['F', 'H', 'J', 'L', 'N', 'P', 'S'];
         foreach ($colsMoney as $col) $sheet->getStyle($col . $f)->getNumberFormat()->setFormatCode($currencyFormat);
         $colsQty = ['G', 'I', 'K', 'M', 'O', 'Q', 'R'];
         foreach ($colsQty as $col) $sheet->getStyle($col . $f)->getNumberFormat()->setFormatCode($qtyFormat);
      };

      // 5. ESCRIBIR ITEMS REGULARES
      foreach ($items_regulares as $value) {
         $em->refresh($value);

         // ---Inyectar Unpaid Actual y Previo desde la Web ---
         if (isset($mapa_datos_web[$value->getId()])) {
            $d = $mapa_datos_web[$value->getId()];
            $value->setUnpaidQty($d['unpaid_qty']);
            $value->setUnpaidFromPrevious($d['unpaid_from_previous']); // Inyectamos el previo correcto
         }
         // ----------------------------------------------------------------

         $this->EscribirFilaItem($objWorksheet, $fila, $item_number, $value, [], $allInvoicesHistory, $invoiceItemRepo, $currentInvoiceId);

         $qty_this_period = $value->getQuantity();
         $qty_brought_forward = $value->getQuantityBroughtForward() ? $value->getQuantityBroughtForward() : 0;
         $final_invoiced_qty = $qty_this_period + $qty_brought_forward;

         $objWorksheet->setCellValue("R{$fila}", $final_invoiced_qty);
         $objWorksheet->setCellValue("S{$fila}", $final_invoiced_qty * $value->getPrice());

         $aplicarFormatoFila($objWorksheet, $fila);
         $item_number++;
         $fila++;
      }

      // 6. ESCRIBIR CHANGE ORDERS
      if (!empty($items_change_order)) {

         // Estilos
         $styleHeaderCO = [
            'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => '000000']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'E7E7E7']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']]]
         ];
         $styleSeparator = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_NONE]],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE]
         ];

         $esPrimerGrupo = true;

         foreach ($items_change_order as $group_key => $group_items) {
            $month = '';
            $year = '';
            if ($group_key !== 'no-date' && !empty($group_items)) {
               $d = $group_items[0]->getProjectItem()->getChangeOrderDate();
               if ($d) {
                  $month = $d->format('F');
                  $year = $d->format('Y');
               }
            }

            if ($month) {
               // A. Fila Separadora (Solo 1 vez)
               if ($esPrimerGrupo) {
                  $objWorksheet->getStyle("A{$fila}:S{$fila}")->applyFromArray($styleSeparator);
                  $objWorksheet->getRowDimension($fila)->setRowHeight(10);
                  $fila++;
                  $esPrimerGrupo = false;
               }

               // B. Título
               $titulo = strtoupper("CHANGE ORDER IN {$month} {$year}");
               $objWorksheet->setCellValue('B' . $fila, $titulo);
               $objWorksheet->mergeCells("B{$fila}:S{$fila}");
               $objWorksheet->getStyle("B{$fila}:S{$fila}")->applyFromArray($styleHeaderCO);
               $objWorksheet->getStyle("A{$fila}")->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]);
               $objWorksheet->getRowDimension($fila)->setRowHeight(22);
               $fila++;
            }

            // C. Items
            foreach ($group_items as $value) {
               $em->refresh($value);

               // --- [CORRECCIÓN] Inyectar Unpaid Actual y Previo ---
               if (isset($mapa_datos_web[$value->getId()])) {
                  $d = $mapa_datos_web[$value->getId()];
                  $value->setUnpaidQty($d['unpaid_qty']);
                  $value->setUnpaidFromPrevious($d['unpaid_from_previous']); // Inyectamos el previo correcto
               }
               // ----------------------------------------------------

               $this->EscribirFilaItem($objWorksheet, $fila, $item_number, $value, [], $allInvoicesHistory, $invoiceItemRepo, $currentInvoiceId);

               $qty_this_period = $value->getQuantity();
               $qty_brought_forward = $value->getQuantityBroughtForward() ? $value->getQuantityBroughtForward() : 0;
               $final_invoiced_qty = $qty_this_period + $qty_brought_forward;

               $objWorksheet->setCellValue("R{$fila}", $final_invoiced_qty);
               $objWorksheet->setCellValue("S{$fila}", $final_invoiced_qty * $value->getPrice());

               $aplicarFormatoFila($objWorksheet, $fila);
               $item_number++;
               $fila++;
            }
         }
      }

      // 7. TOTALES FOOTER
      $last_data_row = $fila_footer_inicio - 1;
      if ($last_data_row < $start_row_data) $last_data_row = $start_row_data;

      // Sumas Excel existentes
      $objWorksheet->setCellValue('H' . $fila_footer_inicio, "=SUM(H{$start_row_data}:H{$last_data_row})");
      $objWorksheet->setCellValue('J' . $fila_footer_inicio, "=SUM(J{$start_row_data}:J{$last_data_row})");
      $objWorksheet->setCellValue('L' . $fila_footer_inicio, "=SUM(L{$start_row_data}:L{$last_data_row})");
      $objWorksheet->setCellValue('O' . $fila_footer_inicio, "=SUM(O{$start_row_data}:O{$last_data_row})");

      $objWorksheet->setCellValue('O' . $fila_footer_inicio, "TOTAL AMT THIS PERIOD:");
      $objWorksheet->getStyle('O' . $fila_footer_inicio)->getFont()->setBold(true);
      $objWorksheet->getStyle('O' . $fila_footer_inicio)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

      $objWorksheet->setCellValue('P' . $fila_footer_inicio, "=SUM(P{$start_row_data}:P{$last_data_row})");
      $objWorksheet->getStyle('P' . $fila_footer_inicio)->getNumberFormat()->setFormatCode('"$"#,##0.00');

      $objWorksheet->setCellValue('R' . $fila_footer_inicio, "TOTAL BILLED AMOUNT:");
      $objWorksheet->getStyle('R' . $fila_footer_inicio)->getFont()->setBold(true);
      $objWorksheet->getStyle('R' . $fila_footer_inicio)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

      $objWorksheet->setCellValue('S' . $fila_footer_inicio, "=SUM(S{$start_row_data}:S{$last_data_row})");
      $objWorksheet->getStyle('S' . $fila_footer_inicio)->getNumberFormat()->setFormatCode('"$"#,##0.00');

      // ---TOTAL PENDING BALANCE  ---
      $objWorksheet->setCellValue('M' . $fila_footer_inicio, "TOTAL PENDING BALANCE:");
      $objWorksheet->setCellValue('N' . $fila_footer_inicio, "=SUM(N{$start_row_data}:N{$last_data_row})");
      $objWorksheet->getStyle('N' . $fila_footer_inicio)->getNumberFormat()->setFormatCode('"$"#,##0.00');


      // 8. RETAINAGE Y GUARDAR
      $std_retainage = (float)$project_entity->getRetainagePercentage();
      $red_retainage = (float)$project_entity->getRetainageAdjustmentPercentage();
      $target_completion = (float)$project_entity->getRetainageAdjustmentCompletion();
      $contract_amount = (float)$project_entity->getContractAmount();

      $percentage_used_for_display = $std_retainage;
      $threshold_amount = 0;
      if ($contract_amount > 0 && $target_completion > 0) {
         $threshold_amount = $contract_amount * ($target_completion / 100);
      }

      $total_retainage_accumulated = 0;
      $running_paid_accumulated = 0;
      $current_retainage_amount = 0;

      $allInvoices = $invoiceRepo->findBy(
         ['project' => $project_id],
         ['startDate' => 'ASC', 'invoiceId' => 'ASC']
      );

      foreach ($allInvoices as $inv) {
         $paid_this_invoice_retainage_base = 0;
         $invItems = $invoiceItemRepo->findBy(['invoice' => $inv]);

         foreach ($invItems as $item) {
            if ($item->getProjectItem()->getApplyRetainage()) {
               $paid_this_invoice_retainage_base += $item->getPaidAmount();
            }
         }

         $pct_to_use = $std_retainage;
         if ($threshold_amount > 0 && ($running_paid_accumulated + $paid_this_invoice_retainage_base) >= $threshold_amount) {
            $pct_to_use = $red_retainage;
         }

         $retainage_calculated = $paid_this_invoice_retainage_base * ($pct_to_use / 100);

         foreach ($inv->getReimbursementHistories() as $history) {
            $retainage_calculated -= (float)$history->getAmount();
         }

         $total_retainage_accumulated += $retainage_calculated;
         $running_paid_accumulated += $paid_this_invoice_retainage_base;

         if ($inv->getInvoiceId() == $invoice_id) {
            $percentage_used_for_display = $pct_to_use;
            $current_retainage_amount = $retainage_calculated;
            break;
         }
      }

      $objWorksheet->setCellValue('R' . $fila_retainage, "CURRENT RETAINAGE @ " . number_format($percentage_used_for_display, 2) . "%");
      $objWorksheet->getStyle('R' . $fila_retainage)->getFont()->setSize(10)->setBold(true);
      $objWorksheet->getStyle('R' . $fila_retainage)->getAlignment()->setWrapText(true);
      $objWorksheet->getStyle('R' . $fila_retainage)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      $objWorksheet->getRowDimension($fila_retainage)->setRowHeight(40);

      $objWorksheet->setCellValue('S' . $fila_retainage, $current_retainage_amount);
      $objWorksheet->getStyle('S' . $fila_retainage)->getNumberFormat()->setFormatCode('"$"#,##0.00');
      $objWorksheet->getStyle('S' . $fila_retainage)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

      $objWorksheet->setCellValue('J' . $fila_retainage, $total_retainage_accumulated);
      $objWorksheet->getStyle('J' . $fila_retainage)->getNumberFormat()->setFormatCode('"$"#,##0.00');
      $objWorksheet->getStyle('J' . $fila_retainage)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

      $fila_amount_due = $fila_retainage + 1;
      $objWorksheet->setCellValue('R' . $fila_amount_due, "CURRENT AMOUNT DUE:");
      $objWorksheet->getStyle('R' . $fila_amount_due)->getFont()->setSize(10)->setBold(true);
      $objWorksheet->getStyle('R' . $fila_amount_due)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

      $objWorksheet->setCellValue('S' . $fila_amount_due, "=S{$fila_footer_inicio}-S{$fila_retainage}");
      $objWorksheet->getStyle('S' . $fila_amount_due)->getNumberFormat()->setFormatCode('"$"#,##0.00');
      $objWorksheet->getStyle('S' . $fila_amount_due)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

      $columna_btd = 'J';
      $fila_C = $fila_retainage + 1;
      $celda_C_valor = $columna_btd . $fila_C;
      $fila_A = $fila_retainage - 1;
      $fila_B = $fila_retainage;
      $formula_C = "={$columna_btd}{$fila_A}-{$columna_btd}{$fila_B}";

      $objWorksheet->setCellValue($celda_C_valor, $formula_C);
      $objWorksheet->getStyle($celda_C_valor)->getNumberFormat()->setFormatCode('"$"#,##0.00');
      $objWorksheet->getStyle($celda_C_valor)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

      $fichero = $project_entity->getProjectNumber() . "-Invoice" . $invoice_entity->getNumber() . ".xlsx";
      $objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
      $objWriter->save("uploads" . DIRECTORY_SEPARATOR . "invoice" . DIRECTORY_SEPARATOR . $fichero);
      $objPHPExcel->disconnectWorksheets();
      unset($objPHPExcel);

      return $this->ObtenerURL() . 'uploads/invoice/' . $fichero;
   }

   // Función auxiliar
   private function EscribirFilaItem($objWorksheet, $fila, $item_number, $value, $styleArray, $allInvoicesHistory, $invoiceItemRepo, $currentInvoiceId)
   {
      $price = $value->getPrice();
      $contract_qty = $value->getProjectItem()->getQuantity();

      $qty = $value->getQuantity();
      $qbf = $value->getQuantityBroughtForward();

      // Cálculo de totales actuales
      $final_qty = $qty + $qbf;
      $qty_completed = $value->getQuantity() + $value->getQuantityFromPrevious();

      // usamos getUnpaidFromPrevious()
      // Si el valor es null, ponemos 0.
      $unpaid_prev_qty = $value->getUnpaidFromPrevious() ? $value->getUnpaidFromPrevious() : 0;
      $unpaid_prev_amount = $unpaid_prev_qty * $price;
      // -----------------------

      $unpaid_qty = $value->getUnpaidQty();
      $unpaid_amount = $unpaid_qty * $price;

      $unit = $value->getProjectItem()->getItem()->getUnit() ? $value->getProjectItem()->getItem()->getUnit()->getDescription() : '';

      $objWorksheet
         ->setCellValue('A' . $fila, $item_number)
         ->setCellValue('B' . $fila, $value->getProjectItem()->getItem()->getName())
         ->setCellValue('E' . $fila, $unit)
         ->setCellValue('F' . $fila, $price)
         ->setCellValue('G' . $fila, $contract_qty)
         ->setCellValue('H' . $fila, $contract_qty * $price)
         ->setCellValue('I' . $fila, $qty_completed)
         ->setCellValue('J' . $fila, $qty_completed * $price)

         // COLUMNAS K y L ACTUALIZADAS
         ->setCellValue('K' . $fila, $unpaid_prev_qty)    // Unpaid Qty From Previous
         ->setCellValue('L' . $fila, $unpaid_prev_amount) // Unpaid Amount From Previous

         ->setCellValue('M' . $fila, $unpaid_qty)
         ->setCellValue('N' . $fila, $unpaid_qty * $price)

         ->setCellValue('O' . $fila, $qty)           // QTY THIS PERIOD
         ->setCellValue('P' . $fila, $qty * $price); // AMOUNT THIS PERIOD

      $objWorksheet->mergeCells("B{$fila}:D{$fila}");
      if (!empty($styleArray)) {
         $objWorksheet->getStyle("A{$fila}:R{$fila}")->applyFromArray($styleArray);
      }
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

         // Agregar sum_boned_project y bone_price para cálculo de X e Y en JavaScript
         if (!empty($items)) {
            $arreglo_resultado['sum_boned_project'] = $items[0]['sum_boned_project'] ?? 0;
            $arreglo_resultado['bone_price'] = $items[0]['bone_price'] ?? 0;
         } else {
            $arreglo_resultado['sum_boned_project'] = 0;
            $arreglo_resultado['bone_price'] = 0;
         }

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

      $currentInvoice = $this->getDoctrine()->getRepository(Invoice::class)->find($invoice_id);
      if (!$currentInvoice) {
         return $items;
      }
      $currentInvoiceId = (int)$currentInvoice->getInvoiceId();
      $project_id = $currentInvoice->getProject()->getProjectId();

      /** @var InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
      $allInvoices = $invoiceRepo->ListarInvoicesRangoFecha('', $project_id, '', '', '');
      $this->sortInvoicesByStartDateAndId($allInvoices);

      foreach ($lista as $key => $value) {

         $contract_qty = $value->getProjectItem()->getQuantity();
         $price = $value->getPrice();
         $contract_amount = $contract_qty * $price; // Definimos contract_amount que faltaba

         // --- LÓGICA DE UNPAID QTY ---
         $project_item_id = $value->getProjectItem()->getId();

         // 1. Obtener historial
         $allInvoiceItems = $invoiceItemRepo->ListarInvoicesDeItem($project_item_id);
         $invoiceItemMap = [];
         foreach ($allInvoiceItems as $invoiceItem) {
            $invId = (int)$invoiceItem->getInvoice()->getInvoiceId();
            $invoiceItemMap[$invId] = $invoiceItem;
         }

         $historialQty = 0.0;
         $historialPaid = 0.0;

         // Valor por defecto (si es un invoice nuevo, asumimos que es el último acumulado)
         $unpaidQtySpecific = 0.0;
         $unpaidPrevSpecific = 0.0;    // Para el Unpaid Anterior
         $lastLoopUnpaid = 0.0;
         $foundSpecific = false;

         // 2. Recorrer línea de tiempo
         foreach ($allInvoices as $inv) {
            $loopInvId = (int)$inv->getInvoiceId();

            // Buscar datos del item en este punto de la historia
            $invItem = $invoiceItemMap[$loopInvId] ?? null;

            $currentQbf = ($invItem) ? (float)$invItem->getQuantityBroughtForward() : 0.0;
            $iQty = ($invItem) ? (float)$invItem->getQuantity() : 0.0;
            $iPaid = ($invItem) ? (float)$invItem->getPaidQty() : 0.0;

            // Calcular Unpaid en este punto del tiempo
            $tempUnpaid = $this->calculateInvoiceUnpaidQty(
               $historialQty,
               $historialPaid,
               $currentQbf
            );

            // CORRECCIÓN CRÍTICA: 
            // Si el invoice del bucle es el que estamos mirando, CAPTURAMOS ese valor y no lo soltamos.
            if ($loopInvId === $currentInvoiceId) {
               $unpaidQtySpecific = $tempUnpaid;
               $unpaidPrevSpecific = $lastLoopUnpaid;
               $foundSpecific = true;
            }

            $lastLoopUnpaid = $tempUnpaid;

            // ACUMULAR PARA EL FUTURO: Solo sumamos la cantidad real.
            $historialQty += $iQty;
            $historialPaid += $iPaid;
         }

         // Si por alguna razón el invoice actual no estaba en la lista (caso raro), usamos el último valor calculado
         if (!$foundSpecific) {
            // Lógica para nuevos invoices que aún no están en $allInvoices
            $unpaidQtySpecific = $this->calculateInvoiceUnpaidQty($historialQty, $historialPaid, $value->getQuantityBroughtForward());
            $unpaidPrevSpecific = $lastLoopUnpaid;
         }

         $unpaid_qty = $unpaidQtySpecific;
         // -------------------------------------

         $quantity = $value->getQuantity();
         $quantity_from_previous = $value->getQuantityFromPrevious();
         $quantity_brought_forward = $value->getQuantityBroughtForward();

         $quantity_completed = $quantity + $quantity_from_previous;
         $quantity_final = $quantity + $quantity_brought_forward;

         $total_amount = $quantity_completed * $price;
         $amount_from_previous = $quantity_from_previous * $price;
         $amount_completed = $quantity_completed * $price;
         $amount_final = $quantity_final * $price;

         $paid_qty = $value->getPaidQty();
         $unpaid_amount = $unpaid_qty * $price;
         $unpaid_from_previous = $unpaidPrevSpecific;

         /** @var ProjectItemHistoryRepository $historyRepo */
         $historyRepo = $this->getDoctrine()->getRepository(ProjectItemHistory::class);
         $has_quantity_history = $historyRepo->TieneHistorialCantidad($project_item_id);
         $has_price_history = $historyRepo->TieneHistorialPrecio($project_item_id);


         $items[] = [
            "invoice_item_id" => $value->getId(),
            "project_item_id" => $project_item_id,
            "apply_retainage" => $value->getProjectItem()->getApplyRetainage(),
            "boned" => $value->getProjectItem()->getBoned() ? 1 : 0,
            "bone" => $value->getProjectItem()->getItem()->getBone() ? 1 : 0,
            "item_id" => $value->getProjectItem()->getItem()->getItemId(),
            "item" => $value->getProjectItem()->getItem()->getName(),
            "unit" => $value->getProjectItem()->getItem()->getUnit() != null ? $value->getProjectItem()->getItem()->getUnit()->getDescription() : '',
            "contract_qty" => $contract_qty,
            "quantity_old" => $value->getProjectItem()->getQuantityOld() ?? '',
            "price" => $price,
            "price_old" => $value->getProjectItem()->getPriceOld() ?? '',
            "contract_amount" => $contract_amount,
            "quantity_from_previous" => $quantity_from_previous,
            "unpaid_from_previous" => $unpaid_from_previous,
            "quantity" => $quantity,
            "quantity_completed" => $quantity_completed,
            "amount" => $value->getQuantity() * $price,
            "total_amount" => $total_amount,
            "amount_from_previous" => $amount_from_previous,
            "amount_completed" => $amount_completed,
            "paid_qty" => $paid_qty,
            "unpaid_qty" => $unpaid_qty, // AHORA SÍ: El valor específico de este invoice
            "unpaid_amount" => $unpaid_amount,
            "quantity_brought_forward" => $quantity_brought_forward,
            "quantity_final" => $quantity_final,
            "amount_final" => $amount_final,
            "principal" => $value->getProjectItem()->getPrincipal(),
            "change_order" => $value->getProjectItem()->getChangeOrder(),
            "change_order_date" => $value->getProjectItem()->getChangeOrderDate() != null ? $value->getProjectItem()->getChangeOrderDate()->format('m/d/Y') : '',
            "has_quantity_history" => $has_quantity_history,
            "has_price_history" => $has_price_history,
            "posicion" => $key
         ];
      }

      // Calcular SUM_BONED_PROJECT y Bone Price para que JavaScript pueda calcular X e Y
      /** @var ProjectItemRepository $projectItemRepo */
      $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
      $sum_boned_project = $projectItemRepo->TotalBonedProjectItems($project_id);
      $bone_price = $projectItemRepo->TotalBonePriceProjectItems($project_id);

      // Agregar estos valores a cada item para que JavaScript los use
      foreach ($items as &$item) {
         $item['sum_boned_project'] = $sum_boned_project;
         $item['bone_price'] = $bone_price;
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

         // Flush para que los items estén disponibles en la BD antes de recalcular
         $em->flush();

         // Actualizar unpaid_qty cuando se modifica quantity_brought_forward
         $this->ActualizarUnpaidQtyPorQuantityBroughtForward($entity, $items);

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
      $em->flush(); // Flush para obtener el invoice_id

      // items
      $this->SalvarItems($entity, $items);

      // Flush para que los items estén disponibles en la BD antes de recalcular
      $em->flush();

      // Actualizar unpaid_qty cuando se modifica quantity_brought_forward
      $this->ActualizarUnpaidQtyPorQuantityBroughtForward($entity, $items);

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

      // Determinar si es el primer invoice verificando si hay invoices anteriores en la BD
      // Usamos quantity_from_previous del primer item para determinar si hay invoices anteriores
      // Si quantity_from_previous == 0, no hay invoices anteriores, entonces es el primer invoice
      $isFirstInvoice = true;
      if (!empty($items)) {
         $first_item = $items[0];
         $quantity_from_previous = $first_item->quantity_from_previous ?? 0;
         // Si quantity_from_previous > 0, hay invoices anteriores, entonces NO es el primero
         if ($quantity_from_previous > 0) {
            $isFirstInvoice = false;
         }
      }

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

         // Leer los demás valores del JSON
         $quantity = isset($value->quantity) ? (float)$value->quantity : 0;
         $quantity_brought_forward = isset($value->quantity_brought_forward) ? (float)$value->quantity_brought_forward : 0;

         $invoice_item_entity->setQuantity($quantity);
         $invoice_item_entity->setPrice(isset($value->price) ? (float)$value->price : 0);
         $invoice_item_entity->setQuantityBroughtForward($quantity_brought_forward);

         // NO guardar unpaid_qty aquí - se calculará después en ActualizarUnpaidQtyPorQuantityBroughtForward
         // El frontend envía unpaid_qty pero lo ignoramos porque debe recalcularse según las reglas de quantity_brought_forward

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
         // Usar TotalInvoiceFinalAmountThisPeriod para calcular el total (suma de Final Amount This Period)
         $total = $invoiceItemRepo->TotalInvoiceFinalAmountThisPeriod($invoice_id);

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
    * ActualizarUnpaidQtyPorQuantityBroughtForward
    * Recalcula unpaid_qty para el invoice afectado y todos los posteriores cuando se modifica QBF
    * 
    * Implementa las reglas:
    * - Regla 1 (sin pagos): unpaidQty = SUM(quantity prev) - QBF(actual)
    * - Regla 2 (con pagos):
    *   - baseDebtPrev = max(0, SUM(quantity prev) - SUM(paid_qty prev))
    *   - si SUM(paid_qty prev) > SUM(qbf prev) => QBF desactivado => unpaid=baseDebtPrev
    *   - si no => QBF activo => unpaid=max(0, baseDebtPrev - QBF(actual))
    * 
    * @param Invoice $currentInvoice El invoice que se está modificando
    * @param array $items Los items modificados (con project_item_id)
    * @return void
    */
   private function ActualizarUnpaidQtyPorQuantityBroughtForward($currentInvoice, $items)
   {
      $project_id = $currentInvoice->getProject()->getProjectId();
      $current_invoice_id = $currentInvoice->getInvoiceId();

      /** @var InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);

      // Obtener todos los invoices del proyecto ordenados por fecha
      $allInvoices = $invoiceRepo->ListarInvoicesRangoFecha('', $project_id, '', '', '');

      $this->sortInvoicesByStartDateAndId($allInvoices);

      $invoiceIndexById = [];
      foreach ($allInvoices as $idx => $invoice) {
         /** @var Invoice $invoice */
         $invoiceIndexById[(int) $invoice->getInvoiceId()] = (int) $idx;
      }

      // Para cada item modificado, recalcular desde el invoice afectado hacia adelante
      foreach ($items as $itemData) {
         // --- CAMBIO 3: CÁLCULO SIMPLIFICADO PARA GUARDAR ---
         $historialQty = 0.0;
         $historialPaid = 0.0;

         // Recorremos SIEMPRE desde el principio (0)
         for ($i = 0; $i < count($allInvoices); $i++) {
            $invId = (int)$allInvoices[$i]->getInvoiceId();
            $invItem = $invoiceItemMap[$invId] ?? null;

            // Datos actuales
            $currentQbf = 0.0;
            $iQty = 0.0;
            $iPaid = 0.0;

            if ($invItem) {
               // Usamos el override si es el invoice actual del formulario
               $currentQbf = ($invId === (int)$current_invoice_id)
                  ? (isset($itemData->quantity_brought_forward) ? (float)$itemData->quantity_brought_forward : 0.0)
                  : (float)$invItem->getQuantityBroughtForward();

               $iQty = (float)$invItem->getQuantity();
               $iPaid = (float)$invItem->getPaidQty();
            }

            // 1. Calcular: (SumQtyPrev - SumPaidPrev) - QBF Actual
            $nuevoUnpaid = $this->calculateInvoiceUnpaidQty($historialQty, $historialPaid, $currentQbf);

            // 2. Guardar en BD (Si existe el item)
            if ($invItem) {
               $invItem->setUnpaidQty($nuevoUnpaid);
               $invItem->setUnpaidFromPrevious($nuevoUnpaid);
               $invItem->setQuantityBroughtForward($currentQbf);
            }

            // 3. Sumar al historial para el siguiente invoice
            $historialQty += $iQty;
            $historialPaid += $iPaid;
         }
      }
   }
}
