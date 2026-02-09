<?php

namespace App\Utils\Admin;

use App\Entity\Item;
use App\Entity\Project;
use App\Entity\Invoice;
use App\Entity\InvoiceItem;

use App\Entity\ProjectItem;
use App\Entity\ProjectItemHistory;
use App\Entity\SyncQueueQbwc;
use App\Repository\DataTrackingItemRepository;
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
    * RecalcularBonProyecto: aplica la regla de tope Bond Quantity ≤ 1 en el proyecto.
    * Por cada invoice (orden: start_date, invoice_id):
    * - X = Bond Quantity calculado = SumBondedInvoiceItems(invoice) / SumBondedProject(project)
    * - Disponible = 1 - Bond Quantity Used (acumulado de invoices anteriores)
    * - Bond Quantity Aplicado = min(X, Disponible)
    * - Bond Amount (Y) = Bond General × Bond Quantity Aplicado
    * - Actualizar acumulado. Si no queda disponible, aplicar 0.
    *
    * @param int|string $project_id
    */
   public function RecalcularBonProyecto($project_id): void
   {
      $project_id = (int) $project_id;
      $em = $this->getDoctrine()->getManager();
      $project = $this->getDoctrine()->getRepository(Project::class)->find($project_id);
      if (!$project) {
         return;
      }
      /** @var ProjectItemRepository $projectItemRepo */
      $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
      $bondGeneral = (float) $projectItemRepo->TotalBondAmountProjectItems($project_id);
      $sumBondedProject = (float) $projectItemRepo->TotalBondedProjectItems($project_id);

      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);

      /** @var InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
      $allInvoices = $invoiceRepo->ListarInvoicesRangoFecha('', (string) $project_id, '', '', '');
      $this->sortInvoicesByStartDateAndId($allInvoices);

      $MAX_BON_QUANTITY = 1.0;
      $bonQuantityUsed = 0.0;

      foreach ($allInvoices as $invoice) {
         /** @var Invoice $invoice */
         if ($bonQuantityUsed >= $MAX_BON_QUANTITY) {
            $invoice->setBonQuantity(0.0);
            $invoice->setBonAmount(0.0);
            $em->persist($invoice);
            continue;
         }

         // X = Bond Quantity calculado para este invoice
         $sumBondedInvoice = (float) $invoiceItemRepo->SumBondedInvoiceItems($invoice->getInvoiceId());
         $x = 0.0;
         if ($sumBondedProject > 0) {
            $x = $sumBondedInvoice / $sumBondedProject;
         }
         if ($x < 0) {
            $x = 0.0;
         }
         if ($x > 1.0) {
            $x = 1.0;
         }

         $available = $MAX_BON_QUANTITY - $bonQuantityUsed;
         $applied = min($x, $available);
         $bonAmount = round($bondGeneral * $applied, 2);

         $invoice->setBonQuantity($applied);
         $invoice->setBonAmount($bonAmount);
         $em->persist($invoice);

         $bonQuantityUsed += $applied;
      }

      $em->flush();
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
    * ExportarExcel: Exporta el invoice a Excel o PDF.
    * Tanto Excel como PDF usan el retainage del invoice (invoice_retainage_calculated / invoice_current_retainage),
    * no el retainage del módulo de Payments. Ver README_RETAINAGE.md.
    *
    * @param int|string $invoice_id
    * @param string     $format 'excel' o 'pdf'
    * @return string|null URL del archivo generado
    */
   public function ExportarExcel($invoice_id, $format = 'excel')
   {
      // 0. OPTIMIZACIÓN: Aumentar recursos para evitar timeout en local
      set_time_limit(300);
      ini_set('memory_limit', '512M');

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
         $mapa_datos_web[$dato['invoice_item_id']] = [
            'unpaid_qty' => $dato['unpaid_qty'],
            'unpaid_from_previous' => $dato['unpaid_from_previous']
         ];
      }

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

      // 2b. Separar Bond del resto: Bond va siempre al final de ítems regulares, antes de change orders
      $items_regulares_sin_bond = [];
      $bondInvoiceItem = null;
      foreach ($items_regulares as $value) {
         if ($value->getProjectItem()->getItem()->getBond()) {
            $bondInvoiceItem = $value;
         } else {
            $items_regulares_sin_bond[] = $value;
         }
      }
      // Extraer Bond también de change orders (si está ahí) para ubicarlo siempre en el mismo lugar
      $bondInvoiceItemFromCO = null;
      foreach ($items_change_order as $key => $group_items) {
         $sin_bond = [];
         foreach ($group_items as $value) {
            if ($value->getProjectItem()->getItem()->getBond()) {
               $bondInvoiceItemFromCO = $value;
            } else {
               $sin_bond[] = $value;
            }
         }
         $items_change_order[$key] = $sin_bond;
      }

      // 2c. Bond no en invoice: ítems Bond del proyecto que no están como línea en el invoice
      $projectItemIdsEnInvoice = array_map(function ($ii) { return $ii->getProjectItem()->getId(); }, $items);
      /** @var ProjectItemRepository $projectItemRepo */
      $projectItemRepo = $em->getRepository(ProjectItem::class);
      $bondProjectItems = array_filter(
         $projectItemRepo->ListarBondProjectItems($project_id),
         function ($pi) use ($projectItemIdsEnInvoice) { return !in_array($pi->getId(), $projectItemIdsEnInvoice); }
      );
      $bondProjectItems = array_values($bondProjectItems);

      // 3. CARGAR EXCEL Y DEFINIR ESTILOS
      Cell::setValueBinder(new AdvancedValueBinder());

      $styleLeft = [
         'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
         'font' => ['name' => 'Calibri', 'size' => 11, 'bold' => false],
         'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
      ];
      $styleRight = [
         'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
         'font' => ['name' => 'Calibri', 'size' => 11, 'bold' => false],
         'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER]
      ];

      $reader = IOFactory::createReader('Xlsx');
      $templatePath = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'bundles' . DIRECTORY_SEPARATOR . 'metronic8' . DIRECTORY_SEPARATOR . 'excel' . DIRECTORY_SEPARATOR . 'invoice.xlsx';
      $objPHPExcel = $reader->load($templatePath);
      $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);

      // ============================================================
      //  >>> APLICAR DIMENSIONES DEL ENCABEZADO Para el Logo <<<
      // ============================================================

      // Fila 1: 10px / 7.50
      $objWorksheet->getRowDimension('1')->setRowHeight(7.50);

      // Fila 2: 29px / 21.75
      $objWorksheet->getRowDimension('2')->setRowHeight(21.75);

      // Filas 3 a 9: 28px / 21.00 (Donde va el Logo)
      for ($i = 3; $i <= 9; $i++) {
         $objWorksheet->getRowDimension($i)->setRowHeight(21.00);
      }

      // Fila 10: 21px / 15.75
      $objWorksheet->getRowDimension('10')->setRowHeight(15.75);

      // Fila 11: 63px / 47.25 (Donde dice "NOTES")
      $objWorksheet->getRowDimension('11')->setRowHeight(47.25);

      // Fila 12: 20px / 15.00
      $objWorksheet->getRowDimension('12')->setRowHeight(15.00);

      // CALCULO DE ESPACIO
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

      // CÁLCULO EXACTO DE FILAS (Bond = 1 fila fija al final de regulares, antes de change orders)
      $hay_bond = ($bondInvoiceItem !== null || $bondInvoiceItemFromCO !== null || !empty($bondProjectItems));
      $filas_necesarias = count($items_regulares_sin_bond) + ($hay_bond ? 1 : 0);
      if (!empty($items_change_order)) {
         $esPrimerGrupoCalculo = true;
         foreach ($items_change_order as $group_key => $group) {
            $filas_necesarias += count($group);
            if ($group_key !== 'no-date') {
               if ($esPrimerGrupoCalculo) {
                  $filas_necesarias += 2;
                  $esPrimerGrupoCalculo = false;
               } else {
                  $filas_necesarias += 1;
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
         $sheet->getStyle("A{$f}:S{$f}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE);
         $sheet->getStyle("E{$f}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
         $sheet->getRowDimension($f)->setRowHeight(18);
         $colsMoney = ['F', 'H', 'J', 'L', 'N', 'P', 'S'];
         foreach ($colsMoney as $col) $sheet->getStyle($col . $f)->getNumberFormat()->setFormatCode($currencyFormat);
         $colsQty = ['G', 'I', 'K', 'M', 'O', 'Q', 'R'];
         foreach ($colsQty as $col) $sheet->getStyle($col . $f)->getNumberFormat()->setFormatCode($qtyFormat);
      };

      // --- INICIALIZAR VARIABLES DE SUMA PARA PDF ---
      $sum_H_contract       = 0;
      $sum_J_completed      = 0;
      $sum_L_previous_bill  = 0;  // PREVIOUS BILL AMOUNT (Final Amount This Period del invoice anterior)
      $sum_N_pending        = 0;
      $sum_P_this_period    = 0;
      $sum_S_billed         = 0;

      // 5. ESCRIBIR ITEMS REGULARES (sin Bond; el Bond va siempre en 5b)
      foreach ($items_regulares_sin_bond as $value) {
         $em->refresh($value);

         if (isset($mapa_datos_web[$value->getId()])) {
            $d = $mapa_datos_web[$value->getId()];
            $value->setUnpaidQty($d['unpaid_qty']);
            $value->setUnpaidFromPrevious($d['unpaid_from_previous']);
         }

         $prevBill = $this->EscribirFilaItem($objWorksheet, $fila, $item_number, $value, [], $allInvoicesHistory, $invoiceItemRepo, $currentInvoiceId);

         // Datos para el Excel
         $qty_this_period = $value->getQuantity();
         $qty_brought_forward = $value->getQuantityBroughtForward() ? $value->getQuantityBroughtForward() : 0;
         $final_invoiced_qty = $qty_this_period + $qty_brought_forward;

         // --- ACUMULAR PARA PDF (Cálculo manual) ---
         $price = $value->getPrice();
         $qty_completed = $value->getQuantity() + $value->getQuantityFromPrevious();

         $sum_H_contract      += ($value->getProjectItem()->getQuantity() * $price);
         $sum_J_completed     += ($qty_completed * $price);
         $sum_L_previous_bill += $prevBill[1];   // PREVIOUS BILL AMOUNT
         $sum_N_pending       += $prevBill[3];   // PENDING BALANCE (BTD) = como en Payments
         $sum_P_this_period   += ($value->getQuantity() * $price);
         $sum_S_billed        += ($final_invoiced_qty * $price);
         // ------------------------------------------

         $objWorksheet->setCellValue("R{$fila}", $final_invoiced_qty);
         $objWorksheet->setCellValue("S{$fila}", $final_invoiced_qty * $value->getPrice());

         $aplicarFormatoFila($objWorksheet, $fila);

         $item_number++;
         $fila++;
      }

      // 5b. ESCRIBIR FILA BOND (siempre en el mismo lugar: al final de ítems regulares, antes de change orders)
      if ($hay_bond) {
         $bon_qty = $invoice_entity->getBonQuantity() !== null ? (float) $invoice_entity->getBonQuantity() : 0.0;
         $bon_amt = $invoice_entity->getBonAmount() !== null ? (float) $invoice_entity->getBonAmount() : 0.0;
         $bondItem = $bondInvoiceItem ?? $bondInvoiceItemFromCO;
         if ($bondItem !== null) {
            $em->refresh($bondItem);
            if (isset($mapa_datos_web[$bondItem->getId()])) {
               $d = $mapa_datos_web[$bondItem->getId()];
               $bondItem->setUnpaidQty($d['unpaid_qty']);
               $bondItem->setUnpaidFromPrevious($d['unpaid_from_previous']);
            }
            $prevBill = $this->EscribirFilaItem($objWorksheet, $fila, $item_number, $bondItem, [], $allInvoicesHistory, $invoiceItemRepo, $currentInvoiceId);
            $qty_this_period = $bondItem->getQuantity();
            $qty_brought_forward = $bondItem->getQuantityBroughtForward() ? $bondItem->getQuantityBroughtForward() : 0;
            $final_invoiced_qty = $qty_this_period + $qty_brought_forward;
            $price = $bondItem->getPrice();
            $qty_completed = $bondItem->getQuantity() + $bondItem->getQuantityFromPrevious();
            $sum_H_contract      += ($bondItem->getProjectItem()->getQuantity() * $price);
            $sum_J_completed     += ($qty_completed * $price);
            $sum_L_previous_bill += $prevBill[1];
            $sum_P_this_period   += ($bondItem->getQuantity() * $price);
            $sum_S_billed        += ($final_invoiced_qty * $price);
            $objWorksheet->setCellValue('M' . $fila, $bon_qty);
            $objWorksheet->setCellValue('N' . $fila, $bon_amt);
            $sum_N_pending += $bon_amt;
            $objWorksheet->setCellValue("R{$fila}", $final_invoiced_qty);
            $objWorksheet->setCellValue("S{$fila}", $final_invoiced_qty * $price);
         } else {
            $projectItem = $bondProjectItems[0];
            $bondResult = $this->EscribirFilaItemBond($objWorksheet, $fila, $item_number, $projectItem);
            $sum_H_contract += $bondResult['contract_amount'];
            $objWorksheet->setCellValue('M' . $fila, $bon_qty);
            $objWorksheet->setCellValue('N' . $fila, $bon_amt);
            $sum_N_pending += $bon_amt;
            $objWorksheet->setCellValue("R{$fila}", 0);
            $objWorksheet->setCellValue("S{$fila}", 0);
         }
         $aplicarFormatoFila($objWorksheet, $fila);
         $item_number++;
         $fila++;
      }

      // 6. ESCRIBIR CHANGE ORDERS
      if (!empty($items_change_order)) {
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
               if ($esPrimerGrupo) {
                  $objWorksheet->getStyle("A{$fila}:S{$fila}")->applyFromArray($styleSeparator);
                  $objWorksheet->getRowDimension($fila)->setRowHeight(10);
                  $fila++;
                  $esPrimerGrupo = false;
               }
               $titulo = strtoupper("CHANGE ORDER IN {$month} {$year}");
               $objWorksheet->setCellValue('B' . $fila, $titulo);
               $objWorksheet->mergeCells("B{$fila}:D{$fila}");
               $objWorksheet->getStyle("B{$fila}:S{$fila}")->applyFromArray($styleHeaderCO);
               $objWorksheet->getStyle("A{$fila}")->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]);
               $objWorksheet->getRowDimension($fila)->setRowHeight(22);
               $fila++;
            }

            foreach ($group_items as $value) {
               $em->refresh($value);
               if (isset($mapa_datos_web[$value->getId()])) {
                  $d = $mapa_datos_web[$value->getId()];
                  $value->setUnpaidQty($d['unpaid_qty']);
                  $value->setUnpaidFromPrevious($d['unpaid_from_previous']);
               }

               $prevBill = $this->EscribirFilaItem($objWorksheet, $fila, $item_number, $value, [], $allInvoicesHistory, $invoiceItemRepo, $currentInvoiceId);

               $price = $value->getPrice();
               $qty_this_period = $value->getQuantity();
               $qty_brought_forward = $value->getQuantityBroughtForward() ? $value->getQuantityBroughtForward() : 0;
               $final_invoiced_qty = $qty_this_period + $qty_brought_forward;

               // --- ACUMULAR PARA PDF (Igual que arriba) ---
               $qty_completed = $value->getQuantity() + $value->getQuantityFromPrevious();

               $sum_H_contract      += ($value->getProjectItem()->getQuantity() * $price);
               $sum_J_completed     += ($qty_completed * $price);
               $sum_L_previous_bill += $prevBill[1];   // PREVIOUS BILL AMOUNT
               $sum_N_pending       += $prevBill[3];   // PENDING BALANCE (BTD) = como en Payments
               $sum_P_this_period   += ($value->getQuantity() * $price);
               $sum_S_billed        += ($final_invoiced_qty * $price);
               // --------------------------------------------

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

      // ==========================================
      // BIFURCACIÓN: PDF (Valores) vs EXCEL (Fórmulas)
      // ==========================================
      if ($format === 'pdf') {
         // USAMOS LAS VARIABLES PHP (Sin Fórmulas)
         $objWorksheet->setCellValue('H' . $fila_footer_inicio, $sum_H_contract);
         $objWorksheet->setCellValue('J' . $fila_footer_inicio, $sum_J_completed);
         $objWorksheet->setCellValue('L' . $fila_footer_inicio, $sum_L_previous_bill);  // PREVIOUS BILL AMOUNT (suma de col L)
         $objWorksheet->setCellValue('N' . $fila_footer_inicio, $sum_N_pending);
         $objWorksheet->setCellValue('P' . $fila_footer_inicio, $sum_P_this_period);
         $objWorksheet->setCellValue('S' . $fila_footer_inicio, $sum_S_billed);
      } else {
         // USAMOS FÓRMULAS DE EXCEL
         $objWorksheet->setCellValue('H' . $fila_footer_inicio, "=SUM(H{$start_row_data}:H{$last_data_row})");
         $objWorksheet->setCellValue('J' . $fila_footer_inicio, "=SUM(J{$start_row_data}:J{$last_data_row})");
         $objWorksheet->setCellValue('L' . $fila_footer_inicio, "=SUM(L{$start_row_data}:L{$last_data_row})");
         $objWorksheet->setCellValue('N' . $fila_footer_inicio, "=SUM(N{$start_row_data}:N{$last_data_row})");
         $objWorksheet->setCellValue('P' . $fila_footer_inicio, "=SUM(P{$start_row_data}:P{$last_data_row})");
         $objWorksheet->setCellValue('S' . $fila_footer_inicio, "=SUM(S{$start_row_data}:S{$last_data_row})");
      }

      // Etiquetas y Estilos comunes (O se usa para la etiqueta "TOTAL AMT THIS PERIOD:", no para la suma)
      $objWorksheet->setCellValue('M' . $fila_footer_inicio, "TOTAL PENDING BALANCE:");
      $objWorksheet->getStyle('N' . $fila_footer_inicio)->getNumberFormat()->setFormatCode('"$"#,##0.00');

      $objWorksheet->setCellValue('O' . $fila_footer_inicio, "TOTAL AMT THIS PERIOD:");
      $objWorksheet->getStyle('O' . $fila_footer_inicio)->getFont()->setBold(true);
      $objWorksheet->getStyle('O' . $fila_footer_inicio)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      $objWorksheet->getStyle('P' . $fila_footer_inicio)->getNumberFormat()->setFormatCode('"$"#,##0.00');

      $objWorksheet->setCellValue('R' . $fila_footer_inicio, "TOTAL BILLED AMOUNT:");
      $objWorksheet->getStyle('R' . $fila_footer_inicio)->getFont()->setBold(true);
      $objWorksheet->getStyle('R' . $fila_footer_inicio)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      $objWorksheet->getStyle('S' . $fila_footer_inicio)->getNumberFormat()->setFormatCode('"$"#,##0.00');


      // 8. LESS RETAINERS (Excel/PDF): mismo cálculo que la vista (CalcularRetainageEfectivoParaInvoice)
      $retainage_efectivo = $this->CalcularRetainageEfectivoParaInvoice($invoice_id);
      $current_retainage_amount = $retainage_efectivo['effective_current'];
      $total_retainage_accumulated = $retainage_efectivo['total_retainage_accumulated'];

      $std_retainage = (float)$project_entity->getRetainagePercentage();
      $invoice_current_base = (float)($invoice_entity->getInvoiceCurrentRetainage() ?? 0);
      $percentage_used_for_display = ($invoice_current_base > 0 && $current_retainage_amount !== 0.0)
         ? ($current_retainage_amount / $invoice_current_base * 100) : $std_retainage;

      // Escritura
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

      // Amount Due (Bifurcado)
      $fila_amount_due = $fila_retainage + 1;
      $objWorksheet->setCellValue('R' . $fila_amount_due, "CURRENT AMOUNT DUE:");
      $objWorksheet->getStyle('R' . $fila_amount_due)->getFont()->setSize(10)->setBold(true);
      $objWorksheet->getStyle('R' . $fila_amount_due)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

      if ($format === 'pdf') {
         // MODO PDF: Resta manual con variables ($sum_S_billed)
         $amount_due_val = $sum_S_billed - $current_retainage_amount;
         $objWorksheet->setCellValue('S' . $fila_amount_due, $amount_due_val);
      } else {
         // MODO EXCEL: Fórmula
         $objWorksheet->setCellValue('S' . $fila_amount_due, "=S{$fila_footer_inicio}-S{$fila_retainage}");
      }
      $objWorksheet->getStyle('S' . $fila_amount_due)->getNumberFormat()->setFormatCode('"$"#,##0.00');
      $objWorksheet->getStyle('S' . $fila_amount_due)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

      // Columna J Balance (Bifurcado)
      $columna_btd = 'J';
      $fila_C = $fila_retainage + 1;
      $celda_C_valor = $columna_btd . $fila_C;

      if ($format === 'pdf') {
         // MODO PDF
         $balance_j_val = $sum_J_completed - $total_retainage_accumulated;
         $objWorksheet->setCellValue($celda_C_valor, $balance_j_val);
      } else {
         // MODO EXCEL
         $fila_A = $fila_retainage - 1;
         $fila_B = $fila_retainage;
         $formula_C = "={$columna_btd}{$fila_A}-{$columna_btd}{$fila_B}";
         $objWorksheet->setCellValue($celda_C_valor, $formula_C);
      }
      $objWorksheet->getStyle($celda_C_valor)->getNumberFormat()->setFormatCode('"$"#,##0.00');
      $objWorksheet->getStyle($celda_C_valor)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

      // Usamos "A1" como inicio y "T" + la última fila como final.
      $rango_impresion = "A1:T{$fila_amount_due}";

      $objWorksheet->getPageSetup()->setPrintArea($rango_impresion);

      // Opcional: Centrar en la página al imprimir
      $objWorksheet->getPageSetup()->setHorizontalCentered(true);
      $nombre_base = $project_entity->getProjectNumber() . "-Invoice" . $invoice_entity->getNumber();

      // ==========================================
      // GENERACIÓN DEL ARCHIVO
      // ==========================================
      if ($format === 'pdf') {
         $fichero = $nombre_base . ".pdf";

         try {
            if (!class_exists('\Mpdf\Mpdf')) {
               throw new \Exception('La librería MPDF no está instalada.');
            }

            // Configuración Visual PDF
            $objWorksheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            $objWorksheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LEGAL);

            // [AJUSTE] Márgenes superiores mayores para evitar solapamiento con el logo
            $objWorksheet->getPageMargins()->setTop(1.0);
            $objWorksheet->getPageMargins()->setRight(0.2);
            $objWorksheet->getPageMargins()->setLeft(0.2);
            $objWorksheet->getPageMargins()->setBottom(0.5);

            // [AJUSTE] Definir área de impresión exacta
            $objWorksheet->getPageSetup()->setPrintArea("A1:T{$fila_amount_due}");

            // [AJUSTE] Escala para que quepa en 1 página de ancho
            $objWorksheet->getPageSetup()->setFitToPage(true);
            $objWorksheet->getPageSetup()->setFitToWidth(1);
            $objWorksheet->getPageSetup()->setFitToHeight(0);

            // [AJUSTE] Anchos de columna para que el texto "Retainage" no se corte
            $objWorksheet->getColumnDimension('R')->setWidth(32);
            $objWorksheet->getColumnDimension('S')->setWidth(25);

            $objWorksheet->setShowGridlines(false);
            $objWorksheet->getPageSetup()->setHorizontalCentered(true);

            while (ob_get_level()) {
               ob_end_clean();
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($objPHPExcel);
            // Fórmulas desactivadas (ya las calculamos manualmente)
            $writer->setPreCalculateFormulas(false);

            $path_archivo = "uploads" . DIRECTORY_SEPARATOR . "invoice" . DIRECTORY_SEPARATOR . $fichero;
            $directorio = dirname($path_archivo);
            if (!is_dir($directorio)) {
               mkdir($directorio, 0777, true);
            }

            $writer->save($path_archivo);
         } catch (\Exception $e) {
            error_log("Error PDF: " . $e->getMessage());
            return null;
         }
      } else {
         // EXCEL NORMAL
         $fichero = $nombre_base . ".xlsx";
         $path_archivo = "uploads" . DIRECTORY_SEPARATOR . "invoice" . DIRECTORY_SEPARATOR . $fichero;
         $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');
         $writer->save($path_archivo);
      }

      $objPHPExcel->disconnectWorksheets();
      unset($objPHPExcel);

      return $this->ObtenerURL() . 'uploads/invoice/' . $fichero;
   }

   /**
    * EscribirFilaItem: Escribe una fila de ítem en el Excel/PDF.
    * Columna K = PREVIOUS BILL QTY (Final Invoiced Quantity del invoice anterior).
    * Columna L = PREVIOUS BILL AMOUNT (Final Amount This Period del invoice anterior).
    * Columna M = PENDING QTY (BTD): quantity_final - paid_qty (mismo criterio que en Payments).
    * Columna N = PENDING BALANCE (BTD): pending_qty_btd * price.
    *
    * @return array [previous_bill_qty, previous_bill_amount, pending_qty_btd, pending_balance_btd] para totales del footer en PDF
    */
   private function EscribirFilaItem($objWorksheet, $fila, $item_number, $value, $styleArray, $allInvoicesHistory, $invoiceItemRepo, $currentInvoiceId)
   {
      $price = $value->getPrice();
      $contract_qty = $value->getProjectItem()->getQuantity();

      $qty = $value->getQuantity();
      $qbf = $value->getQuantityBroughtForward();

      // Cálculo de totales actuales
      $qty_completed = $value->getQuantity() + $value->getQuantityFromPrevious();

      // K y L: PREVIOUS BILL QTY y PREVIOUS BILL AMOUNT = Final Invoiced Qty y Final Amount This Period del invoice anterior
      $previous_bill_qty = 0.0;
      $previous_bill_amount = 0.0;
      $prevInvoice = null;
      foreach ($allInvoicesHistory as $inv) {
         if ((int) $inv->getInvoiceId() === (int) $currentInvoiceId) {
            break;
         }
         $prevInvoice = $inv;
      }
      if ($prevInvoice !== null) {
         $prev_items = $invoiceItemRepo->ListarItems($prevInvoice->getInvoiceId());
         $project_item_id = $value->getProjectItem()->getId();
         foreach ($prev_items as $prevItem) {
            if ($prevItem->getProjectItem()->getId() === $project_item_id) {
               $prev_qty = (float) $prevItem->getQuantity();
               $prev_qbf = $prevItem->getQuantityBroughtForward() !== null ? (float) $prevItem->getQuantityBroughtForward() : 0.0;
               $previous_bill_qty = $prev_qty + $prev_qbf;
               $previous_bill_amount = $previous_bill_qty * (float) $prevItem->getPrice();
               break;
            }
         }
      }

      // PENDING QTY (BTD) y PENDING BALANCE (BTD): mismo criterio que en Payments
      // En Payments: unpaid_qty = quantity_final - paid_qty (lo que falta por pagar de este invoice)
      $quantity_final = $qty + ($qbf ?? 0.0);
      $paid_qty = $value->getPaidQty() !== null ? (float) $value->getPaidQty() : 0.0;
      $pending_qty_btd = max(0.0, $quantity_final - $paid_qty);
      $pending_balance_btd = $pending_qty_btd * $price;

      // Si hay una nota con override_unpaid_qty (Payments), usar ese valor en M y N también.
      // No aplicar para ítem Bond: M y N del Bond se fijan después con bon_quantity/bon_amount.
      if (!$value->getProjectItem()->getItem()->getBond()) {
         $notes = $this->ListarNotesDeItemInvoice($value->getId());
         foreach ($notes as $note) {
            if (isset($note['override_unpaid_qty']) && $note['override_unpaid_qty'] !== null && $note['override_unpaid_qty'] !== '') {
               $pending_qty_btd = max(0.0, (float) $note['override_unpaid_qty']);
               $pending_balance_btd = $pending_qty_btd * $price;
               break;
            }
         }
      }

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

         ->setCellValue('K' . $fila, $previous_bill_qty)    // PREVIOUS BILL QTY = Final Invoiced Quantity del invoice anterior
         ->setCellValue('L' . $fila, $previous_bill_amount)  // PREVIOUS BILL AMOUNT = Final Amount This Period del invoice anterior

         ->setCellValue('M' . $fila, $pending_qty_btd)       // PENDING QTY (BTD) = quantity_final - paid_qty (como en Payments)
         ->setCellValue('N' . $fila, $pending_balance_btd)   // PENDING BALANCE (BTD) = pending_qty_btd * price

         ->setCellValue('O' . $fila, $qty)           // QTY THIS PERIOD
         ->setCellValue('P' . $fila, $qty * $price); // AMOUNT THIS PERIOD

      $objWorksheet->mergeCells("B{$fila}:D{$fila}");
      if (!empty($styleArray)) {
         $objWorksheet->getStyle("A{$fila}:R{$fila}")->applyFromArray($styleArray);
         $objWorksheet->getStyle("A{$fila}:R{$fila}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE);
      }

      return [$previous_bill_qty, $previous_bill_amount, $pending_qty_btd, $pending_balance_btd];
   }

   /**
    * EscribirFilaItemBond: Escribe una fila de ítem BOND en el Excel/PDF.
    * Los items Bond no tienen data tracking ni InvoiceItem; solo aparecen en la exportación.
    * Columnas con valor: description, unit, price, contract qty, contract amount, pending qty btd, pending balance.
    * El resto (I, J, K, L, O, P, R, S) en 0.
    *
    * @return array ['contract_amount', 'pending_balance_btd'] para totales del footer
    */
   private function EscribirFilaItemBond($objWorksheet, $fila, $item_number, ProjectItem $projectItem): array
   {
      $price = (float) $projectItem->getPrice();
      $contract_qty = (float) $projectItem->getQuantity();
      $contract_amount = $contract_qty * $price;
      $unit = $projectItem->getItem()->getUnit() ? $projectItem->getItem()->getUnit()->getDescription() : '';
      $description = $projectItem->getItem()->getName();

      // Pending = full contract (nada facturado vía data tracking)
      $pending_qty_btd = $contract_qty;
      $pending_balance_btd = $contract_amount;

      $objWorksheet
         ->setCellValue('A' . $fila, $item_number)
         ->setCellValue('B' . $fila, $description)
         ->setCellValue('E' . $fila, $unit)
         ->setCellValue('F' . $fila, $price)
         ->setCellValue('G' . $fila, $contract_qty)
         ->setCellValue('H' . $fila, $contract_amount)
         ->setCellValue('I' . $fila, 0)
         ->setCellValue('J' . $fila, 0)
         ->setCellValue('K' . $fila, 0)
         ->setCellValue('L' . $fila, 0)
         ->setCellValue('M' . $fila, $pending_qty_btd)
         ->setCellValue('N' . $fila, $pending_balance_btd)
         ->setCellValue('O' . $fila, 0)
         ->setCellValue('P' . $fila, 0);

      $objWorksheet->mergeCells("B{$fila}:D{$fila}");

      return ['contract_amount' => $contract_amount, 'pending_balance_btd' => $pending_balance_btd];
   }

   /**
    * CalcularYGuardarRetainageInvoice: Calcula y persiste el retainage exclusivo del invoice.
    * No usa ni modifica el retainage de pagos. Base: suma "Final Amount This Period" de items R;
    * porcentaje según avance del contrato (project Retainage tab).
    *
    * @param Invoice $entity Invoice a recalcular
    */
   public function CalcularYGuardarRetainageInvoice(Invoice $entity): void
   {
      $em = $this->getDoctrine()->getManager();
      $invoice_id = $entity->getInvoiceId();
      $project = $entity->getProject();
      if (!$project) {
         $entity->setInvoiceCurrentRetainage(0.0);
         $entity->setInvoiceRetainageCalculated(0.0);
         $em->persist($entity);
         return;
      }
      $project_id = $project->getProjectId();

      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
      /** @var InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);

      $current_retainage = $invoiceItemRepo->TotalInvoiceFinalAmountThisPeriodRetainageOnly((string) $invoice_id);
      $contract_amount = (float) ($project->getContractAmount() ?? 0);

      $allInvoices = $invoiceRepo->ListarInvoicesRangoFecha('', $project_id, '', '', '');
      $this->sortInvoicesByStartDateAndId($allInvoices);

      $accumulated = 0.0;
      foreach ($allInvoices as $inv) {
         $accumulated += $invoiceItemRepo->TotalInvoiceFinalAmountThisPeriodRetainageOnly((string) $inv->getInvoiceId());
         if ($inv->getInvoiceId() == $invoice_id) {
            break;
         }
      }

      $completion_pct = ($contract_amount > 0) ? ($accumulated / $contract_amount * 100) : 0.0;
      $pct_to_use = 0.0;
      if ($project->getRetainage()) {
         $target = (float) ($project->getRetainageAdjustmentCompletion() ?? 0);
         if ($target > 0 && $completion_pct >= $target) {
            $pct_to_use = (float) ($project->getRetainageAdjustmentPercentage() ?? 0);
         } else {
            $pct_to_use = (float) ($project->getRetainagePercentage() ?? 0);
         }
      }

      $invoice_retainage = $current_retainage * ($pct_to_use / 100);

      $entity->setInvoiceCurrentRetainage($current_retainage);
      $entity->setInvoiceRetainageCalculated($invoice_retainage);
      $em->persist($entity);
   }

   /**
    * Calcula el retainage efectivo para un invoice aplicando la misma regla que el Excel/PDF:
    * si total_billed acumulado > contract_amount → current=0, Less=0.
    * Usado en CargarDatosInvoice (vista) y en ExportarExcel.
    *
    * @param int|string $invoice_id
    * @return array{effective_current: float, total_retainage_accumulated: float}
    */
   public function CalcularRetainageEfectivoParaInvoice($invoice_id): array
   {
      $invoice_id = (int) $invoice_id;
      $em = $this->getDoctrine()->getManager();
      $invoiceRepo = $em->getRepository(Invoice::class);
      $invoiceItemRepo = $em->getRepository(InvoiceItem::class);

      $entity = $invoiceRepo->find($invoice_id);
      if (!$entity || !$entity->getProject()) {
         return ['effective_current' => 0.0, 'total_retainage_accumulated' => 0.0];
      }

      $project_entity = $entity->getProject();
      $project_id = $project_entity->getProjectId();
      $total_contract_amount = (float)($project_entity->getContractAmount() ?? 0);

      $allInvoices = $invoiceRepo->findBy(['project' => $project_id], ['startDate' => 'ASC', 'invoiceId' => 'ASC']);
      $this->sortInvoicesByStartDateAndId($allInvoices);

      $current_retainage_amount = 0.0;
      $total_retainage_accumulated = 0.0;
      $cumulative_billed = 0.0;

      foreach ($allInvoices as $inv) {
         if ($inv->getInvoiceRetainageCalculated() === null) {
            $this->CalcularYGuardarRetainageInvoice($inv);
            $em->flush();
            $em->refresh($inv);
         }

         $billed_this_invoice = $invoiceItemRepo->TotalInvoiceFinalAmountThisPeriod((string)$inv->getInvoiceId());
         $cumulative_billed += $billed_this_invoice;

         if ($total_contract_amount > 0 && $cumulative_billed > $total_contract_amount) {
            $effective_current = 0.0;
            $total_retainage_accumulated = 0.0;
         } else {
            $effective_current = (float)($inv->getInvoiceRetainageCalculated() ?? 0);
            $total_retainage_accumulated += $effective_current;
         }

         if ((int)$inv->getInvoiceId() === $invoice_id) {
            $current_retainage_amount = $effective_current;
            break;
         }
      }

      return [
         'effective_current' => $current_retainage_amount,
         'total_retainage_accumulated' => $total_retainage_accumulated
      ];
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

         $invoice = $entity->getInvoice();
         $em->remove($entity);
         $em->flush();

         if ($invoice) {
            $this->CalcularYGuardarRetainageInvoice($invoice);
            $em->flush();
         }

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
         $project_id = $entity->getProject()->getProjectId();
         $this->RecalcularBonProyecto($project_id);
         $entity = $this->getDoctrine()->getRepository(Invoice::class)->find($invoice_id);

         $arreglo_resultado['project_id'] = $project_id;

         $company_id = $entity->getProject()->getCompany()->getCompanyId();
         $arreglo_resultado['company_id'] = $company_id;

         $arreglo_resultado['number'] = $entity->getNumber();
         $arreglo_resultado['start_date'] = $entity->getStartDate()->format('m/d/Y');
         $arreglo_resultado['end_date'] = $entity->getEndDate()->format('m/d/Y');
         $arreglo_resultado['notes'] = $entity->getNotes();
         $arreglo_resultado['paid'] = $entity->getPaid();

         $arreglo_resultado['bon_quantity'] = $entity->getBonQuantity() !== null ? (float) $entity->getBonQuantity() : null;
         $arreglo_resultado['bon_amount'] = $entity->getBonAmount() !== null ? (float) $entity->getBonAmount() : null;

         $em = $this->getDoctrine()->getManager();
         if ($entity->getInvoiceRetainageCalculated() === null && $entity->getInvoiceCurrentRetainage() === null) {
            $this->CalcularYGuardarRetainageInvoice($entity);
            $em->flush();
            $em->refresh($entity);
         }
         // Valores para la vista: Current retainage ($) = retención de este invoice; Less Retainers = acumulado (mismo criterio que Excel)
         $retainage_efectivo = $this->CalcularRetainageEfectivoParaInvoice($invoice_id);
         $effective_current = $retainage_efectivo['effective_current'];

         $arreglo_resultado['invoice_retainage_calculated'] = $effective_current;   // current retainage en $ (este invoice)
         $arreglo_resultado['invoice_current_retainage'] = $effective_current;     // Current Retainer box = current retainage ($)
         $arreglo_resultado['invoice_retainage_accumulated'] = $retainage_efectivo['total_retainage_accumulated']; // Less Retainers (acumulado)

         // projects
         $projects = $this->ListarProjectsDeCompany($company_id);
         $arreglo_resultado['projects'] = $projects;

         // items
         $items = $this->ListarItemsDeInvoice($invoice_id);
         $arreglo_resultado['items'] = $items;

         // Agregar sum_bonded_project, bond_price y bond_general para cálculo de X e Y en JavaScript
         if (!empty($items)) {
            $arreglo_resultado['sum_bonded_project'] = $items[0]['sum_bonded_project'] ?? 0;
            $arreglo_resultado['bond_price'] = $items[0]['bond_price'] ?? 0;
            $arreglo_resultado['bond_general'] = $items[0]['bond_general'] ?? 0;
         } else {
            $arreglo_resultado['sum_bonded_project'] = 0;
            $arreglo_resultado['bond_price'] = 0;
            $arreglo_resultado['bond_general'] = 0;
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
            "bonded" => $value->getProjectItem()->getBonded() ? 1 : 0,
            "bond" => $value->getProjectItem()->getItem()->getBond() ? 1 : 0,
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

      // Calcular SUM_BONDED_PROJECT, Bond Price y Bond General para que JavaScript pueda calcular X e Y
      /** @var ProjectItemRepository $projectItemRepo */
      $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
      $sum_bonded_project = $projectItemRepo->TotalBondedProjectItems($project_id);
      $bond_price = $projectItemRepo->TotalBondPriceProjectItems($project_id);
      $bond_general = $projectItemRepo->TotalBondAmountProjectItems($project_id);

      // Agregar estos valores a cada item para que JavaScript los use
      foreach ($items as &$item) {
         $item['sum_bonded_project'] = $sum_bonded_project;
         $item['bond_price'] = $bond_price;
         $item['bond_general'] = $bond_general;
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

         $this->RecalcularBonProyecto($project_id);

         $this->CalcularYGuardarRetainageInvoice($entity);

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

      $this->RecalcularBonProyecto($project_id);

      $this->CalcularYGuardarRetainageInvoice($entity);

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

   /**
    * ActualizarInvoicesPorCambioDataTracking
    * Cuando se elimina(n) o modifica(n) item(s) del datatracking, recalcula las cantidades
    * solo en el/los invoice(s) cuyo periodo contiene esa fecha. Si la cantidad queda en 0
    * se elimina la línea solo en ese invoice (no en los posteriores #6, #7, etc.).
    *
    * @param int $project_id
    * @param \DateTimeInterface $date Fecha del datatracking afectado
    * @param array|null $project_item_ids IDs de project_item afectados; si null, actualiza todos los items de los invoices encontrados
    */
   public function ActualizarInvoicesPorCambioDataTracking(int $project_id, \DateTimeInterface $date, ?array $project_item_ids = null): void
   {
      /** @var InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
      /** @var DataTrackingItemRepository $dataTrackingItemRepo */
      $dataTrackingItemRepo = $this->getDoctrine()->getRepository(\App\Entity\DataTrackingItem::class);

      $invoices = $invoiceRepo->FindInvoicesContainingDate($project_id, $date);
      if (empty($invoices)) {
         return;
      }

      $em = $this->getDoctrine()->getManager();

      foreach ($invoices as $invoice) {
         /** @var Invoice $invoice */
         $invoiceId = $invoice->getInvoiceId();
         $startDate = $invoice->getStartDate()->format('m/d/Y');
         $endDate = $invoice->getEndDate()->format('m/d/Y');

         $itemsToUpdate = $project_item_ids;
         if ($itemsToUpdate === null) {
            $invoiceItems = $invoiceItemRepo->ListarItems($invoiceId);
            $itemsToUpdate = array_map(function (InvoiceItem $ii) {
               return $ii->getProjectItem()->getId();
            }, $invoiceItems);
            $itemsToUpdate = array_unique($itemsToUpdate);
         }

         foreach ($itemsToUpdate as $project_item_id) {
            $invoiceItem = $invoiceItemRepo->BuscarItem($invoiceId, $project_item_id);
            if ($invoiceItem === null) {
               continue;
            }

            $newQuantity = $dataTrackingItemRepo->TotalQuantity('', $project_item_id, $startDate, $endDate);

            if ($newQuantity == 0.0) {
               $em->remove($invoiceItem);
            } else {
               $invoiceItem->setQuantity($newQuantity);
            }
         }
      }

      $em->flush();
      $this->RecalcularUnpaidQtyProyecto($project_id);

      foreach ($invoices as $invoice) {
         $this->CalcularYGuardarRetainageInvoice($invoice);
      }
      $em->flush();
   }

   /**
    * RecalcularUnpaidQtyProyecto
    * Recalcula quantity_from_previous, unpaid_qty y unpaid_from_previous para todos los invoice items del proyecto.
    * Así, si cambió la cantidad en un invoice anterior (ej. #5), los invoices posteriores (#6, #7, ...)
    * quedan con el total acumulado correcto.
    */
   public function RecalcularUnpaidQtyProyecto(int $project_id): void
   {
      /** @var InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);

      $allInvoices = $invoiceRepo->ListarInvoicesRangoFecha('', (string) $project_id, '', '', '');
      $this->sortInvoicesByStartDateAndId($allInvoices);

      $projectItemIds = [];
      foreach ($allInvoices as $inv) {
         foreach ($invoiceItemRepo->ListarItems($inv->getInvoiceId()) as $ii) {
            $projectItemIds[$ii->getProjectItem()->getId()] = true;
         }
      }
      $projectItemIds = array_keys($projectItemIds);

      foreach ($projectItemIds as $project_item_id) {
         $allInvoiceItems = $invoiceItemRepo->ListarInvoicesDeItem($project_item_id);
         $invoiceItemMap = [];
         foreach ($allInvoiceItems as $ii) {
            $invoiceItemMap[(int) $ii->getInvoice()->getInvoiceId()] = $ii;
         }

         $historialQty = 0.0;
         $historialPaid = 0.0;

         for ($i = 0; $i < count($allInvoices); $i++) {
            $invId = (int) $allInvoices[$i]->getInvoiceId();
            $invItem = $invoiceItemMap[$invId] ?? null;

            if ($invItem) {
               $invItem->setQuantityFromPrevious($historialQty);
            }

            $currentQbf = $invItem ? (float) $invItem->getQuantityBroughtForward() : 0.0;
            $iQty = $invItem ? (float) $invItem->getQuantity() : 0.0;
            $iPaid = $invItem ? (float) $invItem->getPaidQty() : 0.0;

            $nuevoUnpaid = $this->calculateInvoiceUnpaidQty($historialQty, $historialPaid, $currentQbf);

            if ($invItem) {
               $invItem->setUnpaidQty($nuevoUnpaid);
               $invItem->setUnpaidFromPrevious($nuevoUnpaid);
            }

            $historialQty += $iQty;
            $historialPaid += $iPaid;
         }
      }

      $this->getDoctrine()->getManager()->flush();
   }
}
