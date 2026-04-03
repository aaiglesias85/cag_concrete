<?php

namespace App\Utils\Admin;

use App\Entity\Item;
use App\Entity\Project;
use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\InvoiceItemOverridePayment;

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
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\MailerInterface;
use Psr\Log\LoggerInterface;
use Twig\Environment as TwigEnvironment;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xls\Style\CellAlignment;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class InvoiceService extends Base
{
   private TwigEnvironment $twig;

   public function __construct(
      ContainerInterface    $container,
      MailerInterface       $mailer,
      ContainerBagInterface $containerBag,
      Security              $security,
      LoggerInterface       $logger,
      TwigEnvironment       $twig,
      private InvoicePaidQtyOverrideResolver $paidQtyOverrideResolver,
      private InvoiceUnpaidQtyOverrideResolver $unpaidQtyOverrideResolver,
      #[Autowire(lazy: true)]
      private ProjectService $projectService,
   ) {
      parent::__construct($container, $mailer, $containerBag, $security, $logger);
      $this->twig = $twig;
   }

   /**
    * Trazas override payment / unpaid → public/weblog.txt (prefijo [override_invoice]).
    *
    * @param array<string, mixed> $context
    */
   private function logOverrideInvoice(string $step, array $context = []): void
   {
      // Trazas desactivadas (weblog.txt). Descomentar para depurar.
      // $line = $context === []
      //    ? $step
      //    : $step . "\t" . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
      // $this->writelogPublic('[override_invoice] ' . $line, 'weblog.txt');
   }

   /**
    * Depuración QBF / unpaid → public/weblog.txt (prefijo [qbf]).
    *
    * @param array<string, mixed> $context
    */
   private function logQbf(string $step, array $context = []): void
   {
      // Trazas desactivadas (weblog.txt). Descomentar para depurar.
      // $line = $context === []
      //    ? $step
      //    : $step . "\t" . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
      // $this->writelogPublic('[qbf] ' . $line, 'weblog.txt');
   }

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
    * Suma algebraica de getBonAmount() desde el primer invoice del proyecto hasta $invoiceId (inclusive),
    * orden start_date, invoice_id (igual que ObtenerDatosExportacionInvoice).
    */
   private function sumBonAmountThroughInvoice(int $project_id, int $invoiceId): float
   {
      /** @var InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
      $allInvoices = $invoiceRepo->ListarInvoicesRangoFecha('', (string) $project_id, '', '', '');
      $this->sortInvoicesByStartDateAndId($allInvoices);
      $total = 0.0;
      foreach ($allInvoices as $inv) {
         $total += (float) ($inv->getBonAmount() ?? 0);
         if ((int) $inv->getInvoiceId() === $invoiceId) {
            break;
         }
      }
      return $total;
   }

   /**
    * RecalcularBonProyecto: aplica la regla de tope Bond Quantity ≤ 1 en el proyecto,
    * considerando pagos: el consumo acumulado real = Σ bon_quantity (anteriores) − Σ paid_qty Bond (anteriores).
    * Por cada invoice (orden: start_date, invoice_id):
    * - X = Bond Quantity calculado = SumBondedInvoiceItems(invoice) / SumBondedProject(project)
    * - Consumo acumulado real = Σ bon_quantity (invoices anteriores) − Σ paid_qty Bond (invoices anteriores)
    * - Disponible = 1 − consumo acumulado real
    * - Bond Quantity Aplicado = min(X, Disponible). Si disponible ≤ 0, no se asigna más Bond.
    * - Bond Amount (Y) = Bond General × Bond Quantity Aplicado
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
      // Acumulados de invoices ya procesados (para consumo real: bon - paid)
      $totalBonQuantityPrevious = 0.0;
      $totalBondPaidQtyPrevious = 0.0;

      foreach ($allInvoices as $invoice) {
         /** @var Invoice $invoice */
         // Consumo acumulado real = Σ bon_quantity anteriores − Σ paid_qty Bond anteriores
         $consumedReal = $totalBonQuantityPrevious - $totalBondPaidQtyPrevious;
         $available = $MAX_BON_QUANTITY - $consumedReal;
         if ($available <= 0.0) {
            $invoice->setBonQuantity(0.0);
            $invoice->setBonAmount(0.0);
            $em->persist($invoice);
            // Sigue sumando paid_qty de este invoice para el siguiente
            $totalBondPaidQtyPrevious += $this->paidQtyOverrideResolver->sumEffectiveBondPaidQtyForInvoice((int) $invoice->getInvoiceId());
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

         // Nuevo Bond = min(X, disponible). Si supera 1, se limita: aplicado = 1 − consumo acumulado real
         $applied = min($x, $available);
         $applied = round($applied, 5); // Bond qty con 5 decimales
         $bonAmount = round($bondGeneral * $applied, 2);

         $invoice->setBonQuantity($applied);
         $invoice->setBonAmount($bonAmount);
         $em->persist($invoice);

         $totalBonQuantityPrevious += $applied;
         $totalBondPaidQtyPrevious += $this->paidQtyOverrideResolver->sumEffectiveBondPaidQtyForInvoice((int) $invoice->getInvoiceId());
      }

      $em->flush();
   }

   /**
    * RecalcularRetainageYBonPorProyecto: Recalcula los valores de retainage y bond
    * de todos los invoices del proyecto. Se debe llamar cuando cambian las marcas
    * R (apply_retainage) o bonded en ítems del proyecto.
    *
    * @param int|string $project_id
    */
   public function RecalcularRetainageYBonPorProyecto($project_id): void
   {
      $project_id = (string) $project_id;
      $project = $this->getDoctrine()->getRepository(Project::class)->find((int) $project_id);
      if (!$project) {
         return;
      }
      $this->RecalcularBonProyecto($project_id);
      /** @var InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
      $allInvoices = $invoiceRepo->ListarInvoicesRangoFecha('', $project_id, '', '', '');
      $this->sortInvoicesByStartDateAndId($allInvoices);
      $em = $this->getDoctrine()->getManager();
      foreach ($allInvoices as $inv) {
         $this->CalcularYGuardarRetainageInvoice($inv);
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

      $seenOverrideIdsForSeries = [];
      foreach ($allInvoices as $idx => $invoice) {
         /** @var Invoice $invoice */
         $invoiceId = (int) $invoice->getInvoiceId();
         $item = $invoiceItemMap[$invoiceId] ?? null;

         $qty = (float) (($item?->getQuantity()) ?? 0);
         $paid = $item !== null
            ? $this->paidQtyOverrideResolver->paidIncrementForHistorialTimeline($item, $seenOverrideIdsForSeries)
            : 0.0;
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
      $out = max(0.0, $deudaNetaPrev - $currentQbf);
      if (abs($currentQbf) > 1e-12) {
         $this->logQbf('calculateInvoiceUnpaidQty', [
            'sum_prev_qty' => $sumPrevQty,
            'sum_prev_paid' => $sumPrevPaid,
            'current_qbf' => $currentQbf,
            'deuda_neta_prev' => $deudaNetaPrev,
            'unpaid_out' => $out,
         ]);
      }

      return $out;
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
    * ExportarExcel: Exporta el invoice a Excel.
    * Usa ObtenerDatosExportacionInvoice (misma fuente que el PDF). Ver README_RETAINAGE.md.
    *
    * @param int|string $invoice_id
    * @return string|null URL del archivo generado
    */
   public function ExportarExcel($invoice_id)
   {
      set_time_limit(300);
      ini_set('memory_limit', '512M');

      // Fuente única de datos y cálculos (compartida con PDF)
      $data = $this->ObtenerDatosExportacionInvoice($invoice_id);
      if ($data === null) {
         return null;
      }

      $invoice_entity = $data['invoice_entity'];
      $project_entity = $data['project_entity'];
      $company = $data['company'];
      $insp = $data['inspector'];

      // CARGAR EXCEL Y DEFINIR ESTILOS
      Cell::setValueBinder(new AdvancedValueBinder());

      $styleLeft = [
         'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
         'font' => ['name' => 'Calibri', 'size' => 13, 'bold' => false],
         'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
      ];
      $styleRight = [
         'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
         'font' => ['name' => 'Calibri', 'size' => 13, 'bold' => false],
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

      // Número de filas = mismo que en ObtenerDatosExportacionInvoice (rows incluye headers y datos)
      $filas_necesarias = count($data['rows']);
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

      // Escribir todas las filas desde datos canónicos (misma fuente que el PDF)
      foreach ($data['rows'] as $row) {
         if (isset($row['header'])) {
            if ($row['header'] === '') {
               $objWorksheet->getStyle("A{$fila}:S{$fila}")->applyFromArray($styleSeparator);
               $objWorksheet->getRowDimension($fila)->setRowHeight(10);
               $fila++;
            } else {
               $objWorksheet->setCellValue('B' . $fila, $row['header']);
               $objWorksheet->mergeCells("B{$fila}:D{$fila}");
               $objWorksheet->getStyle("B{$fila}:S{$fila}")->applyFromArray($styleHeaderCO);
               $objWorksheet->getStyle("A{$fila}")->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]);
               $objWorksheet->getRowDimension($fila)->setRowHeight(22);
               $fila++;
            }
         } else {
            $this->EscribirFilaDesdeDatos($objWorksheet, $fila, $row);
            $aplicarFormatoFila($objWorksheet, $fila);
            $fila++;
         }
      }

      // TOTALES FOOTER (Excel: fórmulas; valores vienen de $data por consistencia)
      $last_data_row = $fila - 1;
      if ($last_data_row < $start_row_data) $last_data_row = $start_row_data;

      $objWorksheet->setCellValue('H' . $fila_footer_inicio, "=SUM(H{$start_row_data}:H{$last_data_row})");
      $objWorksheet->setCellValue('J' . $fila_footer_inicio, "=SUM(J{$start_row_data}:J{$last_data_row})");
      $objWorksheet->setCellValue('L' . $fila_footer_inicio, "=SUM(L{$start_row_data}:L{$last_data_row})");
      $objWorksheet->setCellValue('N' . $fila_footer_inicio, "=SUM(N{$start_row_data}:N{$last_data_row})");
      $objWorksheet->setCellValue('P' . $fila_footer_inicio, "=SUM(P{$start_row_data}:P{$last_data_row})");
      $objWorksheet->setCellValue('S' . $fila_footer_inicio, "=SUM(S{$start_row_data}:S{$last_data_row})");

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


      // LESS RETAINERS: valores desde $data (misma fuente que PDF)
      $objWorksheet->setCellValue('R' . $fila_retainage, "CURRENT RETAINAGE @ " . number_format($data['percentage_used'], 2) . "%");
      $objWorksheet->getStyle('R' . $fila_retainage)->getFont()->setSize(10)->setBold(true);
      $objWorksheet->getStyle('R' . $fila_retainage)->getAlignment()->setWrapText(true);
      $objWorksheet->getStyle('R' . $fila_retainage)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
      $objWorksheet->getRowDimension($fila_retainage)->setRowHeight(40);

      $objWorksheet->setCellValue('S' . $fila_retainage, $data['current_retainage_amount']);
      $objWorksheet->getStyle('S' . $fila_retainage)->getNumberFormat()->setFormatCode('"$"#,##0.00');
      $objWorksheet->getStyle('S' . $fila_retainage)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

      $objWorksheet->setCellValue('J' . $fila_retainage, $data['total_retainage_accumulated']);
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
      $objWorksheet->setCellValue($celda_C_valor, "={$columna_btd}{$fila_A}-{$columna_btd}{$fila_B}");
      $objWorksheet->getStyle($celda_C_valor)->getNumberFormat()->setFormatCode('"$"#,##0.00');
      $objWorksheet->getStyle($celda_C_valor)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

      // Usamos "A1" como inicio y "T" + la última fila como final.
      $rango_impresion = "A1:T{$fila_amount_due}";

      $objWorksheet->getPageSetup()->setPrintArea($rango_impresion);

      $objWorksheet->getPageSetup()->setHorizontalCentered(true);

      // GENERACIÓN DEL ARCHIVO (solo Excel; PDF se genera en ExportarPdf)
      if (false) { // PDF ahora usa plantilla Twig (ver early return al inicio)
         $fichero = $data['nombre_archivo'] . ".pdf";

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

            // [FIX] Eliminar filas vacías más allá del contenido para evitar páginas en blanco en el PDF.
            // El writer Html/PDF usa getHighestDataRow() y NO el print area, por lo que incluye
            // todas las filas con datos del template; al eliminar las sobrantes el PDF queda en 1 página.
            $highestRow = $objWorksheet->getHighestDataRow();
            if ($highestRow > $fila_amount_due) {
               $objWorksheet->removeRow($fila_amount_due + 1, $highestRow - $fila_amount_due);
            }

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
         $fichero = $data['nombre_archivo'] . ".xlsx";
         $path_archivo = "uploads" . DIRECTORY_SEPARATOR . "invoice" . DIRECTORY_SEPARATOR . $fichero;
         $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');
         $writer->save($path_archivo);
      }

      $objPHPExcel->disconnectWorksheets();
      unset($objPHPExcel);

      return $this->ObtenerURL() . 'uploads/invoice/' . $fichero;
   }

   /**
    * ExportarPdf: Genera el PDF del invoice usando la plantilla Twig (admin/invoice/pdf.html.twig).
    * Usa los mismos datos y cálculos que ExportarExcel para mantener consistencia.
    *
    * @param int|string $invoice_id
    * @return string|null URL del archivo generado
    */
   public function ExportarPdf($invoice_id)
   {
      set_time_limit(300);
      ini_set('memory_limit', '512M');

      $data = $this->PrepararDatosParaPdfTemplate($invoice_id);
      if ($data === null) {
         return null;
      }

      $projectDir = $this->getParameter('kernel.project_dir');
      $publicDir = $projectDir . DIRECTORY_SEPARATOR . 'public';
      $logoFullPath = str_replace('\\', '/', $publicDir . '/bundles/metronic8/img/logo.jpg');
      $data['logo_path'] = file_exists($logoFullPath) ? $logoFullPath : '';

      $fichero = $data['nombre_archivo'] . '.pdf';
      $path_archivo = $projectDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'invoice' . DIRECTORY_SEPARATOR . $fichero;
      $directorio = dirname($path_archivo);
      if (!is_dir($directorio)) {
         mkdir($directorio, 0777, true);
      }

      $html = $this->twig->render('admin/invoice/pdf.html.twig', $data);

      try {
         $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'Legal-L',
            'margin_left' => 4,
            'margin_right' => 4,
            'margin_top' => 4,
            'margin_bottom' => 4,
            'fontsize' => 8,
            'shrink_tables_to_fit' => 6,
         ]);
         $mpdf->WriteHTML($html);
         $mpdf->Output($path_archivo, \Mpdf\Output\Destination::FILE);
         return $this->ObtenerURL() . 'uploads/invoice/' . $fichero;
      } catch (\Exception $e) {
         $this->logger->error('PDF Mpdf falló: ' . $e->getMessage());
         return null;
      }
   }

   /**
    * ObtenerDatosExportacionInvoice: Fuente única de datos y cálculos para exportación Excel y PDF.
    * Toda la lógica de negocio (ítems, totales, retainage) vive aquí; Excel y PDF solo renderizan estos datos.
    * Modificar cálculos o reglas solo en este método para que ambos formatos coincidan.
    *
    * @param int|string $invoice_id
    * @return array|null { invoice_entity, project_entity, company, inspector, rows, totales, retainage, nombre_archivo } o null
    */
   public function ObtenerDatosExportacionInvoice($invoice_id)
   {
      $em = $this->getDoctrine()->getManager();
      $invoiceItemRepo = $em->getRepository(InvoiceItem::class);
      $invoiceRepo = $em->getRepository(Invoice::class);

      $invoice_entity = $invoiceRepo->find($invoice_id);
      if (!$invoice_entity) {
         return null;
      }

      $project_entity = $invoice_entity->getProject();
      $project_id = $project_entity->getProjectId();
      $this->RecalcularRetainageYBonPorProyecto($project_id);
      $invoice_entity = $invoiceRepo->find($invoice_id);

      $datos_web = $this->ListarItemsDeInvoice($invoice_id);
      $mapa_datos_web = [];
      foreach ($datos_web as $dato) {
         $mapa_datos_web[$dato['invoice_item_id']] = [
            'unpaid_qty' => $dato['unpaid_qty'],
            'unpaid_from_previous' => $dato['unpaid_from_previous']
         ];
      }

      $currentInvoiceId = $invoice_id;
      $allInvoicesHistory = $invoiceRepo->ListarInvoicesRangoFecha('', $project_id, '', '', '');
      $this->sortInvoicesByStartDateAndId($allInvoicesHistory);

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

      $items_regulares_sin_bond = [];
      $bondInvoiceItem = null;
      foreach ($items_regulares as $value) {
         if ($value->getProjectItem()->getItem()->getBond()) {
            $bondInvoiceItem = $value;
         } else {
            $items_regulares_sin_bond[] = $value;
         }
      }
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

      $projectItemIdsEnInvoice = array_map(fn($ii) => $ii->getProjectItem()->getId(), $items);
      $projectItemRepo = $em->getRepository(ProjectItem::class);
      $bondProjectItems = array_values(array_filter(
         $projectItemRepo->ListarBondProjectItems($project_id),
         fn($pi) => !in_array($pi->getId(), $projectItemIdsEnInvoice)
      ));
      $hay_bond = ($bondInvoiceItem !== null || $bondInvoiceItemFromCO !== null || !empty($bondProjectItems));

      $company = $project_entity->getCompany();
      $insp = $project_entity->getInspector();

      $rows = [];
      $item_number = 1;
      $sum_H_contract = 0;
      $sum_J_completed = 0;
      $sum_L_previous_bill = 0;
      $sum_N_pending = 0;
      $sum_P_this_period = 0;
      $sum_S_billed = 0;

      foreach ($items_regulares_sin_bond as $value) {
         $em->refresh($value);
         if (isset($mapa_datos_web[$value->getId()])) {
            $d = $mapa_datos_web[$value->getId()];
            $value->setUnpaidQty($d['unpaid_qty']);
            $value->setUnpaidFromPrevious($d['unpaid_from_previous']);
         }
         $rowData = $this->ObtenerFilaItemData($value, $item_number, $allInvoicesHistory, $invoiceItemRepo, $currentInvoiceId);
         $prevBill = $rowData['prev_bill'];
         $qty_this_period = $value->getQuantity();
         $qty_brought_forward = $value->getQuantityBroughtForward() ?: 0;
         $final_invoiced_qty = $qty_this_period + $qty_brought_forward;
         $rowData['billed_qty'] = $final_invoiced_qty;
         $rowData['billed_amount'] = $final_invoiced_qty * $value->getPrice();

         $sum_H_contract += $rowData['contract_amount'];
         $sum_J_completed += $rowData['total_amount_btd'];
         $sum_L_previous_bill += $prevBill[1];
         $sum_N_pending += $prevBill[3];
         $sum_P_this_period += $value->getQuantity() * $value->getPrice();
         $sum_S_billed += $rowData['billed_amount'];

         $rows[] = $rowData;
         $item_number++;
      }

      if ($hay_bond) {
         $bon_qty = $invoice_entity->getBonQuantity() !== null ? (float) $invoice_entity->getBonQuantity() : 0.0;
         $bon_amt = $invoice_entity->getBonAmount() !== null ? (float) $invoice_entity->getBonAmount() : 0.0;
         $prev_bon_qty = 0.0;
         $prev_bon_amt = 0.0;
         $prevInvoiceBond = null;
         foreach ($allInvoicesHistory as $inv) {
            if ((int) $inv->getInvoiceId() === (int) $currentInvoiceId) break;
            $prevInvoiceBond = $inv;
         }
         if ($prevInvoiceBond !== null) {
            $prev_bon_qty = (float) ($prevInvoiceBond->getBonQuantity() ?? 0);
            $prev_bon_amt = (float) ($prevInvoiceBond->getBonAmount() ?? 0);
         }
         $total_bond_qty_to_date = 0.0;
         $total_bond_amt_to_date = 0.0;
         foreach ($allInvoicesHistory as $inv) {
            $total_bond_qty_to_date += (float) ($inv->getBonQuantity() ?? 0);
            $total_bond_amt_to_date += (float) ($inv->getBonAmount() ?? 0);
            if ((int) $inv->getInvoiceId() === (int) $currentInvoiceId) break;
         }

         $bondItem = $bondInvoiceItem ?? $bondInvoiceItemFromCO;
         if ($bondItem !== null) {
            $em->refresh($bondItem);
            if (isset($mapa_datos_web[$bondItem->getId()])) {
               $d = $mapa_datos_web[$bondItem->getId()];
               $bondItem->setUnpaidQty($d['unpaid_qty']);
               $bondItem->setUnpaidFromPrevious($d['unpaid_from_previous']);
            }
            $rowData = $this->ObtenerFilaItemData($bondItem, $item_number, $allInvoicesHistory, $invoiceItemRepo, $currentInvoiceId);
         } else {
            $projectItem = $bondProjectItems[0];
            $rowData = $this->ObtenerFilaItemBondData($projectItem, $item_number);
         }
         $rowData['total_qty_btd'] = $total_bond_qty_to_date;
         $rowData['total_amount_btd'] = $total_bond_amt_to_date;
         $rowData['previous_bill_qty'] = $prev_bon_qty;
         $rowData['previous_bill_amount'] = $prev_bon_amt;
         $rowData['pending_qty'] = 0;
         $rowData['pending_balance'] = 0;
         $rowData['qty_this_period'] = $bon_qty;
         $rowData['amount_this_period'] = $bon_amt;
         $rowData['billed_qty'] = $bon_qty;
         $rowData['billed_amount'] = $bon_amt;

         $sum_H_contract += $rowData['contract_amount'];
         $sum_J_completed += $total_bond_amt_to_date;
         $sum_L_previous_bill += $prev_bon_amt;
         $sum_P_this_period += $bon_amt;
         $sum_S_billed += $bon_amt;

         $rows[] = $rowData;
         $item_number++;
      }

      $all_co_keys = array_keys($items_change_order);
      sort($all_co_keys);
      $esPrimerGrupo = true;
      foreach ($all_co_keys as $group_key) {
         $group_items = $items_change_order[$group_key] ?? [];
         if (empty($group_items)) continue;

         $titulo = '';
         if ($group_key !== 'no-date') {
            $d = $group_items[0]->getProjectItem()->getChangeOrderDate();
            if ($d) {
               $titulo = strtoupper('CHANGE ORDER IN ' . $d->format('F') . ' ' . $d->format('Y'));
            } else {
               $dt = \DateTime::createFromFormat('Y-m', $group_key);
               if ($dt) {
                  $titulo = strtoupper('CHANGE ORDER IN ' . $dt->format('F') . ' ' . $dt->format('Y'));
               }
            }
         }
         if ($titulo === '') $titulo = 'CHANGE ORDER (NO DATE)';

         if ($esPrimerGrupo) {
            $esPrimerGrupo = false;
            $rows[] = ['header' => ''];
         }
         $rows[] = ['header' => $titulo];

         foreach ($group_items as $value) {
            $em->refresh($value);
            if (isset($mapa_datos_web[$value->getId()])) {
               $d = $mapa_datos_web[$value->getId()];
               $value->setUnpaidQty($d['unpaid_qty']);
               $value->setUnpaidFromPrevious($d['unpaid_from_previous']);
            }
            $rowData = $this->ObtenerFilaItemData($value, $item_number, $allInvoicesHistory, $invoiceItemRepo, $currentInvoiceId);
            $prevBill = $rowData['prev_bill'];
            $final_invoiced_qty = $value->getQuantity() + ($value->getQuantityBroughtForward() ?: 0);
            $rowData['billed_qty'] = $final_invoiced_qty;
            $rowData['billed_amount'] = $final_invoiced_qty * $value->getPrice();

            $sum_H_contract += $rowData['contract_amount'];
            $sum_J_completed += $rowData['total_amount_btd'];
            $sum_L_previous_bill += $prevBill[1];
            $sum_N_pending += $prevBill[3];
            $sum_P_this_period += $value->getQuantity() * $value->getPrice();
            $sum_S_billed += $rowData['billed_amount'];

            $rows[] = $rowData;
            $item_number++;
         }
      }

      $retainage_efectivo = $this->CalcularRetainageEfectivoParaInvoice($invoice_id);
      $current_retainage_amount = $retainage_efectivo['effective_current'];
      $total_retainage_accumulated = $retainage_efectivo['total_retainage_accumulated'];
      $std_retainage = (float) $project_entity->getRetainagePercentage();
      $invoice_current_base = (float) ($invoice_entity->getInvoiceCurrentRetainage() ?? 0);
      $percentage_used = ($invoice_current_base > 0 && $current_retainage_amount !== 0.0)
         ? ($current_retainage_amount / $invoice_current_base * 100) : $std_retainage;
      $amount_due = $sum_S_billed - $current_retainage_amount;
      $balance_after_retainage = $sum_J_completed - $total_retainage_accumulated;

      $nombre_archivo = $project_entity->getProjectNumber() . '-Invoice' . $invoice_entity->getNumber();

      return [
         'invoice_entity' => $invoice_entity,
         'project_entity' => $project_entity,
         'company' => $company,
         'inspector' => $insp,
         'rows' => $rows,
         'total_contract_amount' => $sum_H_contract,
         'total_amount_btd' => $sum_J_completed,
         'total_previous_bill' => $sum_L_previous_bill,
         'total_pending_balance' => $sum_N_pending,
         'total_amt_this_period' => $sum_P_this_period,
         'total_billed_amount' => $sum_S_billed,
         'current_retainage_amount' => $current_retainage_amount,
         'total_retainage_accumulated' => $total_retainage_accumulated,
         'percentage_used' => $percentage_used,
         'balance_after_retainage' => $balance_after_retainage,
         'current_amount_due' => $amount_due,
         'nombre_archivo' => $nombre_archivo,
      ];
   }

   /**
    * PrepararDatosParaPdfTemplate: Prepara el array de datos para la plantilla Twig del invoice PDF.
    * Usa ObtenerDatosExportacionInvoice como única fuente de datos/cálculos.
    *
    * @param int|string $invoice_id
    * @return array|null Datos para el template, o null si el invoice no existe
    */
   public function PrepararDatosParaPdfTemplate($invoice_id)
   {
      $data = $this->ObtenerDatosExportacionInvoice($invoice_id);
      if ($data === null) {
         return null;
      }

      $invoice_entity = $data['invoice_entity'];
      $project_entity = $data['project_entity'];
      $company = $data['company'];
      $insp = $data['inspector'];

      $projectDir = $this->getParameter('kernel.project_dir');
      $logoPath = str_replace('\\', '/', $projectDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'bundles' . DIRECTORY_SEPARATOR . 'metronic8' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'logo.jpg');

      $company_name = $company ? $company->getName() : '';
      $company_address = $company ? ($company->getAddress() ?? '') : '';
      $company_phone = $company ? ($company->getPhone() ?? '') : '';
      $company_contact = $company ? ($company->getContactName() ?? '') : '';
      $company_email = $company ? ($company->getEmail() ?? $company->getContactEmail() ?? '') : '';
      $company_contact_email = $company ? ($company->getContactEmail() ?? '') : '';

      return [
         'logo_path' => $logoPath,
         'company_name' => $company_name,
         'company_address' => $company_address,
         'company_phone' => $company_phone,
         'company_fax' => '',
         'company_email' => $company_email,
         'company_contact_email' => $company_contact_email,
         'contractor_name' => $company_name,
         'contractor_phone' => $company_phone,
         'inspector_name' => $insp ? $insp->getName() : '',
         'inspector_phone' => $insp ? $insp->getPhone() : '',
         'contact_name' => $company_contact,
         'contact_email' => $company_contact_email,
         'project_location' => $this->getCountiesDescriptionForProject($project_entity),
         'project_name' => $project_entity->getName(),
         'project_id_number' => $project_entity->getProjectIdNumber(),
         'subcontract' => $project_entity->getSubcontract(),
         'project_number' => $project_entity->getProjectNumber(),
         'invoice_date' => date('m/d/Y'),
         'invoice_number' => $invoice_entity->getNumber(),
         'start_date' => $invoice_entity->getStartDate()->format('m/d/Y'),
         'end_date' => $invoice_entity->getEndDate()->format('m/d/Y'),
         'notes' => $invoice_entity->getNotes() ?? '',
         'rows' => $data['rows'],
         'total_contract_amount' => $data['total_contract_amount'],
         'total_amount_btd' => $data['total_amount_btd'],
         'total_previous_bill' => $data['total_previous_bill'],
         'total_pending_balance' => $data['total_pending_balance'],
         'total_amt_this_period' => $data['total_amt_this_period'],
         'total_billed_amount' => $data['total_billed_amount'],
         'retainage_label' => 'CURRENT RETAINAGE @ ' . number_format($data['percentage_used'], 2) . '%',
         'current_retainage_amount' => $data['current_retainage_amount'],
         'total_retainage_accumulated' => $data['total_retainage_accumulated'],
         'balance_after_retainage' => $data['balance_after_retainage'],
         'current_amount_due' => $data['current_amount_due'],
         'nombre_archivo' => $data['nombre_archivo'],
      ];
   }

   /**
    * ObtenerFilaItemData: Devuelve los datos de una fila de ítem (sin escribir en Excel).
    * @return array Con keys: item_number, description, unit, unit_price, contract_qty, contract_amount, total_qty_btd, total_amount_btd,
    *   previous_bill_qty, previous_bill_amount, pending_qty, pending_balance, qty_this_period, amount_this_period, prev_bill (para totales)
    */
   private function ObtenerFilaItemData($value, $item_number, $allInvoicesHistory, $invoiceItemRepo, $currentInvoiceId): array
   {
      $price = $value->getPrice();
      $contract_qty = $value->getProjectItem()->getQuantity();
      $qty = $value->getQuantity();
      $qty_completed = $value->getQuantity() + $value->getQuantityFromPrevious();

      $previous_bill_qty = 0.0;
      $previous_bill_amount = 0.0;
      $prevInvoice = null;
      foreach ($allInvoicesHistory as $inv) {
         if ((int) $inv->getInvoiceId() === (int) $currentInvoiceId) break;
         $prevInvoice = $inv;
      }
      if ($prevInvoice !== null) {
         foreach ($invoiceItemRepo->ListarItems($prevInvoice->getInvoiceId()) as $prevItem) {
            if ($prevItem->getProjectItem()->getId() === $value->getProjectItem()->getId()) {
               $prev_qty = (float) $prevItem->getQuantity();
               $prev_qbf = $prevItem->getQuantityBroughtForward() !== null ? (float) $prevItem->getQuantityBroughtForward() : 0.0;
               $previous_bill_qty = $prev_qty + $prev_qbf;
               $previous_bill_amount = $previous_bill_qty * (float) $prevItem->getPrice();
               break;
            }
         }
      }

      $pending_qty_btd = 0.0;
      $pending_balance_btd = 0.0;
      if (!$value->getProjectItem()->getItem()->getBond()) {
         $sum_qty_prev = 0.0;
         $sum_paid_prev = 0.0;
         $seenOverrideIdsExcel = [];
         foreach ($allInvoicesHistory as $inv) {
            if ((int) $inv->getInvoiceId() === (int) $currentInvoiceId) break;
            foreach ($invoiceItemRepo->ListarItems($inv->getInvoiceId()) as $prevItem) {
               if ($prevItem->getProjectItem()->getId() === $value->getProjectItem()->getId()) {
                  $sum_qty_prev += (float) $prevItem->getQuantity();
                  $sum_paid_prev += $this->paidQtyOverrideResolver->paidIncrementForHistorialTimeline($prevItem, $seenOverrideIdsExcel);
                  break;
               }
            }
         }
         $notes = $this->ListarNotesDeItemInvoice($value->getId());
         $hasNoteOverride = false;
         foreach ($notes as $note) {
            if (isset($note['override_unpaid_qty']) && $note['override_unpaid_qty'] !== null && $note['override_unpaid_qty'] !== '') {
               $pending_qty_btd = max(0.0, (float) $note['override_unpaid_qty']);
               $pending_balance_btd = $pending_qty_btd * $price;
               $hasNoteOverride = true;
               break;
            }
         }
         if (!$hasNoteOverride) {
            $unpaidOverride = $this->unpaidQtyOverrideResolver->getEffectiveUnpaidQty($value);
            if ($unpaidOverride > 0) {
               $pending_qty_btd = max(0.0, $unpaidOverride);
               $pending_balance_btd = $pending_qty_btd * $price;
            }
         }
      } else {
         $quantity_final = $qty + ($value->getQuantityBroughtForward() ?? 0.0);
         $paid_qty = $this->paidQtyOverrideResolver->getEffectivePaidQty($value);
         $pending_qty_btd = max(0.0, $quantity_final - $paid_qty);
         $pending_balance_btd = $pending_qty_btd * $price;
      }

      $unit = $value->getProjectItem()->getItem()->getUnit()
         ? $value->getProjectItem()->getItem()->getUnit()->getDescription() : '';

      return [
         'item_number' => $item_number,
         'description' => $value->getProjectItem()->getItem()->getName(),
         'unit' => $unit,
         'unit_price' => $price,
         'contract_qty' => $contract_qty,
         'contract_amount' => $contract_qty * $price,
         'total_qty_btd' => $qty_completed,
         'total_amount_btd' => $qty_completed * $price,
         'previous_bill_qty' => $previous_bill_qty,
         'previous_bill_amount' => $previous_bill_amount,
         'pending_qty' => $pending_qty_btd,
         'pending_balance' => $pending_balance_btd,
         'qty_this_period' => $qty,
         'amount_this_period' => $qty * $price,
         'billed_qty' => 0,
         'billed_amount' => 0,
         'prev_bill' => [$previous_bill_qty, $previous_bill_amount, $pending_qty_btd, $pending_balance_btd],
      ];
   }

   /**
    * ObtenerFilaItemBondData: Datos para fila Bond cuando no existe invoice_item Bond.
    */
   private function ObtenerFilaItemBondData(ProjectItem $projectItem, $item_number): array
   {
      $price = (float) $projectItem->getPrice();
      $contract_qty = (float) $projectItem->getQuantity();
      $unit = $projectItem->getItem()->getUnit() ? $projectItem->getItem()->getUnit()->getDescription() : '';
      return [
         'item_number' => $item_number,
         'description' => $projectItem->getItem()->getName(),
         'unit' => $unit,
         'unit_price' => $price,
         'contract_qty' => $contract_qty,
         'contract_amount' => $contract_qty * $price,
         'total_qty_btd' => 0,
         'total_amount_btd' => 0,
         'previous_bill_qty' => 0,
         'previous_bill_amount' => 0,
         'pending_qty' => 0,
         'pending_balance' => 0,
         'qty_this_period' => 0,
         'amount_this_period' => 0,
         'billed_qty' => 0,
         'billed_amount' => 0,
         'prev_bill' => [0, 0, 0, 0],
      ];
   }

   /**
    * EscribirFilaDesdeDatos: Escribe una fila de ítem en el Excel a partir del array de datos
    * devuelto por ObtenerDatosExportacionInvoice (misma estructura que ObtenerFilaItemData).
    * Solo escritura; no realiza cálculos. Usado por ExportarExcel para mantener una sola fuente de datos.
    *
    * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $objWorksheet
    * @param int $fila
    * @param array $rowData keys: item_number, description, unit, unit_price, contract_qty, contract_amount, total_qty_btd, total_amount_btd, previous_bill_qty, previous_bill_amount, pending_qty, pending_balance, qty_this_period, amount_this_period, billed_qty, billed_amount
    */
   private function EscribirFilaDesdeDatos($objWorksheet, $fila, array $rowData): void
   {
      $objWorksheet
         ->setCellValue('A' . $fila, $rowData['item_number'])
         ->setCellValue('B' . $fila, $rowData['description'])
         ->setCellValue('E' . $fila, $rowData['unit'] ?? '')
         ->setCellValue('F' . $fila, $rowData['unit_price'])
         ->setCellValue('G' . $fila, $rowData['contract_qty'])
         ->setCellValue('H' . $fila, $rowData['contract_amount'])
         ->setCellValue('I' . $fila, $rowData['total_qty_btd'])
         ->setCellValue('J' . $fila, $rowData['total_amount_btd'])
         ->setCellValue('K' . $fila, $rowData['previous_bill_qty'])
         ->setCellValue('L' . $fila, $rowData['previous_bill_amount'])
         ->setCellValue('M' . $fila, $rowData['pending_qty'])
         ->setCellValue('N' . $fila, $rowData['pending_balance'])
         ->setCellValue('O' . $fila, $rowData['qty_this_period'])
         ->setCellValue('P' . $fila, $rowData['amount_this_period'])
         ->setCellValue('R' . $fila, $rowData['billed_qty'])
         ->setCellValue('S' . $fila, $rowData['billed_amount']);
      $objWorksheet->mergeCells("B{$fila}:D{$fila}");
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
    * Contexto de retainage del proyecto para cálculo en frontend (invoice en borrador).
    * Devuelve config del proyecto y acumulados de invoices anteriores; el frontend calcula current y total con los ítems.
    *
    * @param string|int $project_id
    * @param int|null $exclude_invoice_id Si se indica, no se incluye este invoice en los acumulados (para edición: "previous" = otros invoices).
    * @return array{contract_amount: float, retainage: bool, retainage_percentage: float, retainage_adjustment_percentage: float, retainage_adjustment_completion: float, accumulated_retainage_amount_previous: float, accumulated_base_retainage_previous: float, total_billed_previous: float}
    */
   public function getRetainageContextForProject($project_id, ?int $exclude_invoice_id = null): array
   {
      $project_id = (string) $project_id;
      $em = $this->getDoctrine()->getManager();
      $project = $em->getRepository(Project::class)->find($project_id);
      if (!$project) {
         return [
            'contract_amount' => 0.0,
            'retainage' => false,
            'retainage_percentage' => 0.0,
            'retainage_adjustment_percentage' => 0.0,
            'retainage_adjustment_completion' => 0.0,
            'accumulated_retainage_amount_previous' => 0.0,
            'accumulated_base_retainage_previous' => 0.0,
            'total_billed_previous' => 0.0,
         ];
      }

      $invoiceItemRepo = $em->getRepository(InvoiceItem::class);
      $invoiceRepo = $em->getRepository(Invoice::class);
      $total_contract_amount = (float) ($project->getContractAmount() ?? 0);

      $allInvoices = $invoiceRepo->ListarInvoicesRangoFecha('', $project_id, '', '', '');
      $this->sortInvoicesByStartDateAndId($allInvoices);

      $accumulated_previous_retainage = 0.0;
      $accumulated_previous_base_retainage = 0.0;
      $cumulative_billed = 0.0;

      foreach ($allInvoices as $inv) {
         if ((int) $inv->getInvoiceId() === (int) $exclude_invoice_id) {
            continue;
         }
         if ($inv->getInvoiceRetainageCalculated() === null) {
            $this->CalcularYGuardarRetainageInvoice($inv);
            $em->flush();
            $em->refresh($inv);
         }

         $billed_this = $invoiceItemRepo->TotalInvoiceFinalAmountThisPeriod((string) $inv->getInvoiceId());
         $cumulative_billed += $billed_this;

         if ($total_contract_amount > 0 && $cumulative_billed > $total_contract_amount) {
            $accumulated_previous_retainage = 0.0;
            $accumulated_previous_base_retainage = 0.0;
         } else {
            $accumulated_previous_retainage += (float) ($inv->getInvoiceRetainageCalculated() ?? 0);
            $accumulated_previous_base_retainage += $invoiceItemRepo->TotalInvoiceFinalAmountThisPeriodRetainageOnly((string) $inv->getInvoiceId());
         }
      }

      return [
         'contract_amount' => $total_contract_amount,
         'retainage' => (bool) $project->getRetainage(),
         'retainage_percentage' => (float) ($project->getRetainagePercentage() ?? 0),
         'retainage_adjustment_percentage' => (float) ($project->getRetainageAdjustmentPercentage() ?? 0),
         'retainage_adjustment_completion' => (float) ($project->getRetainageAdjustmentCompletion() ?? 0),
         'accumulated_retainage_amount_previous' => round($accumulated_previous_retainage, 2),
         'accumulated_base_retainage_previous' => round($accumulated_previous_base_retainage, 2),
         'total_billed_previous' => round($cumulative_billed, 2),
      ];
   }

   /**
    * Bond disponible antes de aplicar este invoice (para preview en frontend al cambiar QBF).
    *
    * @param string|int $project_id
    * @param Invoice $entity invoice actual
    * @return float valor entre 0 y 1
    */
   private function getBonQuantityAvailableBeforeInvoice($project_id, Invoice $entity): float
   {
      $project_id = (string) $project_id;
      $invoice_id = (int) $entity->getInvoiceId();
      $start_date_str = $entity->getStartDate() instanceof \DateTimeInterface
         ? $entity->getStartDate()->format('m/d/Y') : '';

      /** @var \App\Repository\InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);

      $bon_used_before_or_on = (float) $invoiceRepo->SumBonQuantityUsedBeforeOrOnDate($project_id, $start_date_str);
      $bond_paid_before_or_on = $this->paidQtyOverrideResolver->sumEffectiveBondPaidQtyForProjectBeforeOrOnDate((int) $project_id, $start_date_str);

      $this_bon_qty = $entity->getBonQuantity() !== null ? (float) $entity->getBonQuantity() : 0.0;
      $this_bond_paid = $this->paidQtyOverrideResolver->sumEffectiveBondPaidQtyForInvoice($invoice_id);

      $used_before_this = $bon_used_before_or_on - $this_bon_qty;
      $paid_before_this = $bond_paid_before_or_on - $this_bond_paid;
      $consumed_before_this = $used_before_this - $paid_before_this;

      return max(0.0, min(1.0, 1.0 - $consumed_before_this));
   }

   /**
    * Calcula retainage (current y acumulado) para un conjunto de ítems de borrador.
    * Usado desde listarItemsParaInvoice para devolver valores listos para pintar en frontend.
    *
    * @param string|int $project_id
    * @param array $items_draft Cada ítem: quantity, quantity_brought_forward, price, apply_retainage
    * @return array{effective_current_retainage: float, total_retainage_accumulated: float}
    */
   public function getRetainageForDraftItems($project_id, array $items_draft): array
   {
      $ctx = $this->getRetainageContextForProject($project_id);

      $base_current_retainage = 0.0;
      $total_billed_current = 0.0;
      foreach ($items_draft as $item) {
         $qty = (float) ($item['quantity'] ?? 0);
         $qbf = (float) ($item['quantity_brought_forward'] ?? 0);
         $price = (float) ($item['price'] ?? 0);
         $apply_retainage = !empty($item['apply_retainage']);
         $final_amount = ($qty + $qbf) * $price;
         $total_billed_current += $final_amount;
         if ($apply_retainage) {
            $base_current_retainage += $final_amount;
         }
      }

      $contract_amount = (float) ($ctx['contract_amount'] ?? 0);
      $total_billed_previous = (float) ($ctx['total_billed_previous'] ?? 0);
      if ($contract_amount > 0 && $total_billed_previous + $total_billed_current > $contract_amount) {
         return ['effective_current_retainage' => 0.0, 'total_retainage_accumulated' => 0.0];
      }

      $accumulated_retainage_previous = (float) ($ctx['accumulated_retainage_amount_previous'] ?? 0);
      $accumulated_base_previous = (float) ($ctx['accumulated_base_retainage_previous'] ?? 0);
      $retainage_enabled = (bool) ($ctx['retainage'] ?? false);
      $pct_default = (float) ($ctx['retainage_percentage'] ?? 0);
      $pct_adjustment = (float) ($ctx['retainage_adjustment_percentage'] ?? 0);
      $completion_threshold = (float) ($ctx['retainage_adjustment_completion'] ?? 0);

      $total_base_retainage = $accumulated_base_previous + $base_current_retainage;
      $completion_pct = $contract_amount > 0 ? ($total_base_retainage / $contract_amount * 100) : 0.0;
      $pct_to_use = 0.0;
      if ($retainage_enabled && $completion_threshold > 0 && $completion_pct >= $completion_threshold) {
         $pct_to_use = $pct_adjustment;
      } elseif ($retainage_enabled) {
         $pct_to_use = $pct_default;
      }

      $current_retainage = $base_current_retainage * ($pct_to_use / 100);
      $total_accumulated = $accumulated_retainage_previous + $current_retainage;

      return [
         'effective_current_retainage' => round($current_retainage, 2),
         'total_retainage_accumulated' => round($total_accumulated, 2),
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
      $this->logOverrideInvoice('cargar_datos_invoice', ['invoice_id' => (int) $invoice_id]);
      $resultado = array();
      $arreglo_resultado = array();

      $entity = $this->getDoctrine()->getRepository(Invoice::class)
         ->find($invoice_id);
      /** @var Invoice $entity */
      if ($entity != null) {
         $project_id = $entity->getProject()->getProjectId();
         // Recalcular bond y retainage de todo el proyecto y guardar en BD (por si se quitó R o bonded en ítems)
         $this->RecalcularRetainageYBonPorProyecto($project_id);
         $entity = $this->getDoctrine()->getRepository(Invoice::class)->find($invoice_id);

         $arreglo_resultado['project_id'] = $project_id;

         $project = $entity->getProject();
         $arreglo_resultado['contract_amount'] = $project && $project->getContractAmount() !== null ? (float) $project->getContractAmount() : 0.0;

         $company_id = $project->getCompany()->getCompanyId();
         $arreglo_resultado['company_id'] = $company_id;

         $arreglo_resultado['number'] = $entity->getNumber();
         $arreglo_resultado['start_date'] = $entity->getStartDate()->format('m/d/Y');
         $arreglo_resultado['end_date'] = $entity->getEndDate()->format('m/d/Y');
         $arreglo_resultado['notes'] = $entity->getNotes();
         $arreglo_resultado['paid'] = $entity->getPaid();

         $arreglo_resultado['bon_quantity'] = $entity->getBonQuantity() !== null ? (float) $entity->getBonQuantity() : null;
         $arreglo_resultado['bon_amount'] = $entity->getBonAmount() !== null ? (float) $entity->getBonAmount() : null;

         // Suma algebraica de bon_amount hasta este invoice (incl.), misma regla que el Excel — cajitas superiores sin tocar la tabla
         $arreglo_resultado['bond_amount_cumulative_to_date'] = $this->sumBonAmountThroughInvoice($project_id, (int) $invoice_id);

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
         $this->logOverrideInvoice('cargar_datos_invoice_items_loaded', [
            'invoice_id' => (int) $invoice_id,
            'project_id' => $project_id,
            'items_count' => \count($items),
         ]);

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

         // Contexto de retainage para recálculo en frontend al cambiar QBF (excluye este invoice)
         $arreglo_resultado['retainage_context'] = $this->getRetainageContextForProject($project_id, (int) $invoice_id);

         // Bond disponible para este invoice (cuánto queda antes de aplicar este invoice) para preview al cambiar QBF
         $arreglo_resultado['bon_quantity_available'] = $this->getBonQuantityAvailableBeforeInvoice($project_id, $entity);

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
         // OverridePaymentWritelog::writelog('[ListarItemsDeInvoice] ABORT invoice no encontrado invoice_id=' . (string) $invoice_id);
         return $items;
      }
      $currentInvoiceId = (int)$currentInvoice->getInvoiceId();
      $project_id = $currentInvoice->getProject()->getProjectId();
      $invSd = $currentInvoice->getStartDate();
      $invEd = $currentInvoice->getEndDate();
      $this->logOverrideInvoice('listar_items_start', [
         'invoice_id' => $currentInvoiceId,
         'project_id' => $project_id,
         'invoice_period_start' => $invSd !== null ? $invSd->format('Y-m-d') : null,
         'invoice_period_end' => $invEd !== null ? $invEd->format('Y-m-d') : null,
         'invoice_lines_in_request' => \count($lista),
      ]);

      /** @var InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
      $allInvoices = $invoiceRepo->ListarInvoicesRangoFecha('', $project_id, '', '', '');
      $this->sortInvoicesByStartDateAndId($allInvoices);

      foreach ($lista as $key => $value) {
         // El ítem Bond no se muestra en la tabla; los totales superiores usan bon_amount / bond_amount_cumulative_to_date (CargarDatos).
         if ($value->getProjectItem()->getItem()->getBond()) {
            continue;
         }

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

         // Paid: fila del período del invoice. Unpaid ancla: cabecera ≤ mes invoice (puede ser otra fila que la de paid).
         $invPeriodStart = $currentInvoice->getStartDate();
         $invPeriodEnd = $currentInvoice->getEndDate();
         /** @var ?InvoiceItemOverridePayment $latestOverride */
         $latestOverride = null;
         $anchorUnpaidEffective = null;
         $periodOverrideRow = null;
         if ($invPeriodStart !== null && $invPeriodEnd !== null) {
            $periodOverrideRow = $this->paidQtyOverrideResolver->selectOverrideRowForInvoicePeriod(
               (int) $project_item_id,
               $invPeriodStart,
               $invPeriodEnd
            );
         }
         if ($invPeriodStart !== null) {
            $anchorRow = $this->unpaidQtyOverrideResolver->findUnpaidAnchorOverrideRow(
               (int) $project_item_id,
               $invPeriodStart
            );
            if ($anchorRow !== null) {
               $effUnpaid = $this->unpaidQtyOverrideResolver->getEffectiveUnpaidFromOverrideRow($anchorRow);
               if ($effUnpaid !== null) {
                  $latestOverride = $anchorRow;
                  $anchorUnpaidEffective = $effUnpaid;
               }
            }
         }

         /** Cabecera más antigua con unpaid efectivo: partición timeline (facturas anteriores = valor BD) */
         $overridePartitionDate = $this->unpaidQtyOverrideResolver->findEarliestUnpaidOverrideHeaderDate((int) $project_item_id);

         $overrideStartDate = null;
         if ($latestOverride !== null) {
            $overrideStartDate = $latestOverride->getOverridePeriodDate();
         }
         $this->logOverrideInvoice('item_override_row', [
            'invoice_item_id' => $value->getId(),
            'project_item_id' => $project_item_id,
            'period_override_row_id' => $periodOverrideRow !== null ? $periodOverrideRow->getId() : null,
            'latest_override_id' => $latestOverride !== null ? $latestOverride->getId() : null,
            'override_period_date_ymd' => $overrideStartDate !== null ? $overrideStartDate->format('Y-m-d') : null,
            'override_partition_date_ymd' => $overridePartitionDate !== null ? $overridePartitionDate->format('Y-m-d') : null,
            'override_snapshot_unpaid_column' => $latestOverride !== null ? $latestOverride->getUnpaidQty() : null,
            'anchor_unpaid_effective' => $anchorUnpaidEffective,
            'override_snapshot_paid' => $periodOverrideRow !== null ? $periodOverrideRow->getPaidQty() : ($latestOverride !== null ? $latestOverride->getPaidQty() : null),
         ]);

         // Valor por defecto
         $unpaidQtySpecific = 0.0;
         $unpaidPrevSpecific = 0.0;
         $lastLoopUnpaid = 0.0;
         $lastUnpaidOverrideValue = null;
         $foundSpecific = false;

         $seenOverrideIdsTimeline = [];
         $carryIntoCurrentForFallback = null;

         // 2. Recorrer línea de tiempo
         foreach ($allInvoices as $inv) {
            $carryAtLoopStart = $lastUnpaidOverrideValue;
            $loopInvId = (int)$inv->getInvoiceId();
            $invStart = $inv->getStartDate();

            // Buscar datos del item en este punto de la historia
            $invItem = $invoiceItemMap[$loopInvId] ?? null;

            // Partición unpaid: primera cabecera (fecha) con unpaid efectivo en override. Facturas con start
            // estrictamente anteriores no deben mostrar unpaid “congelado” en BD si quedó desalineado respecto
            // al paid (p. ej. override de octubre no debe dejar septiembre con unpaid tomado de un guardado viejo).
            // Aquí: misma fórmula que sin override (qty/paid acumulados + paidIncrementForHistorialTimeline).
            $isAfterOverride = ($overridePartitionDate === null) || ($invStart !== null && $invStart >= $overridePartitionDate);

            $currentQbf = ($invItem) ? (float)$invItem->getQuantityBroughtForward() : 0.0;
            $iQty = ($invItem) ? (float)$invItem->getQuantity() : 0.0;
            $iPaid = ($invItem)
               ? $this->paidQtyOverrideResolver->paidIncrementForHistorialTimeline($invItem, $seenOverrideIdsTimeline)
               : 0.0;

            $tempUnpaid = 0.0;

            if (!$isAfterOverride) {
               // Pre-partición: recalcular desde cadena invoice (no invoice_item.unpaid_qty persistido).
               $tempUnpaid = $this->calculateInvoiceUnpaidQty($historialQty, $historialPaid, $currentQbf);
            } elseif ($latestOverride !== null && $anchorUnpaidEffective !== null) {
               // Desde el override en adelante: base = unpaid efectivo de la ancla (columna o historial)
               $overrideBase = (float) $anchorUnpaidEffective;
               if (
                   $invStart !== null && $overrideStartDate !== null
                   && $this->isSameCalendarMonth($invStart, $overrideStartDate)
               ) {
               // Mismo mes que la cabecera: unpaid = snapshot (el valor del override, sin sumar qty de este mes)
                   // El qty de este mes se suma al próximo
                   $tempUnpaid = $overrideBase;
                   $lastUnpaidOverrideValue = max(0.0, $overrideBase + $iQty - $currentQbf);
               } else {
                  // Meses posteriores: encadenar (ej. snapshot 10 + qty oct 100 → 110 en nov)
                  // unpaid = unpaid_anterior + qty - paid (sí restar paid de estos invoices)
                  if ($lastUnpaidOverrideValue !== null) {
                     $tempUnpaid = $lastUnpaidOverrideValue + $iQty - $iPaid - $currentQbf;
                  } else {
                     // Primer invoice después del override: unpaid = overrideBase + qty (sin restar paid)
                     $tempUnpaid = $overrideBase + $iQty - $currentQbf;
                  }
                  $tempUnpaid = max(0.0, $tempUnpaid);
                  $lastUnpaidOverrideValue = $tempUnpaid;
               }
            } elseif ($lastUnpaidOverrideValue !== null) {
               // Propagar valor anterior
               $tempUnpaid = $lastUnpaidOverrideValue + $iQty - $iPaid - $currentQbf;
               $tempUnpaid = max(0.0, $tempUnpaid);
            } else {
               // Calcular normalmente
               $tempUnpaid = $this->calculateInvoiceUnpaidQty(
                  $historialQty,
                  $historialPaid,
                  $currentQbf
               );
            }

            if (!$isAfterOverride) {
               $branchLabel = 'pre_partition_calculated_unpaid';
            } elseif ($latestOverride !== null && $anchorUnpaidEffective !== null) {
               $branchLabel = 'override_snapshot_or_chain';
            } elseif ($lastUnpaidOverrideValue !== null) {
               $branchLabel = 'propagate_last';
            } else {
               $branchLabel = 'calculate_invoice_unpaid_qty';
            }
            $this->logOverrideInvoice('timeline_iteration', [
               'project_item_id' => $project_item_id,
               'current_invoice_id' => $currentInvoiceId,
               'loop_invoice_id' => $loopInvId,
               'carry_at_loop_start' => $carryAtLoopStart,
               'last_unpaid_carry_end' => $lastUnpaidOverrideValue,
               'is_after_override' => $isAfterOverride,
               'branch' => $branchLabel,
               'temp_unpaid' => $tempUnpaid,
               'i_qty' => $iQty,
               'i_paid_timeline' => $iPaid,
               'current_qbf' => $currentQbf,
               'historial_qty_before' => $historialQty,
            ]);

            // Si el invoice del bucle es el que estamos mirando, CAPTURAMOS ese valor
            if ($loopInvId === $currentInvoiceId) {
               $carryIntoCurrentForFallback = $carryAtLoopStart;
               // QBF: siempre se resta del unpaid mostrado (una sola vez). Primero la deuda “antes de aplicar QBF
               // de esta línea”; luego max(0, esa − QBF). No depende del mes del override.
               if (!$isAfterOverride) {
                  $unpaidBeforeQbfForRow = $this->calculateInvoiceUnpaidQty($historialQty, $historialPaid, 0.0);
               } elseif ($latestOverride !== null && $anchorUnpaidEffective !== null && $isAfterOverride) {
                  if (
                     $invStart !== null && $overrideStartDate !== null
                     && $this->isSameCalendarMonth($invStart, $overrideStartDate)
                  ) {
                     $unpaidBeforeQbfForRow = $carryAtLoopStart !== null
                        ? (float) $carryAtLoopStart
                        : (float) $anchorUnpaidEffective;
                  } elseif ($carryAtLoopStart !== null) {
                     $unpaidBeforeQbfForRow = (float) $carryAtLoopStart;
                  } else {
                     $unpaidBeforeQbfForRow = (float) $anchorUnpaidEffective;
                  }
               } else {
                  $unpaidBeforeQbfForRow = max(0.0, $tempUnpaid + $currentQbf);
               }
               $unpaidOpeningForDisplay = max(0.0, $unpaidBeforeQbfForRow - $currentQbf);
               $this->logQbf('listar_timeline_current_invoice', [
                  'invoice_item_id' => $value->getId(),
                  'project_item_id' => $project_item_id,
                  'loop_invoice_id' => $loopInvId,
                  'current_qbf' => $currentQbf,
                  'override_base' => $anchorUnpaidEffective,
                  'carry_at_loop_start' => $carryAtLoopStart,
                  'temp_unpaid' => $tempUnpaid,
                  'unpaid_before_qbf' => $unpaidBeforeQbfForRow,
                  'unpaid_opening_for_display' => $unpaidOpeningForDisplay,
                  'same_month_as_override_header' => $invStart !== null && $overrideStartDate !== null
                     && $this->isSameCalendarMonth($invStart, $overrideStartDate),
               ]);
             $this->logOverrideInvoice('timeline_at_current_invoice', [
                   'invoice_item_id' => $value->getId(),
                   'project_item_id' => $project_item_id,
                   'loop_invoice_id' => $loopInvId,
                   'carry_at_loop_start' => $carryAtLoopStart,
                   'temp_unpaid' => $tempUnpaid,
                   'unpaid_opening_for_display' => $unpaidOpeningForDisplay,
                   'last_loop_unpaid' => $lastLoopUnpaid,
                   'is_after_override' => $isAfterOverride,
                   'override_partition_date_ymd' => $overridePartitionDate?->format('Y-m-d'),
                   'branch' => !$isAfterOverride
                      ? 'pre_partition_calculated_unpaid'
                      : ($latestOverride !== null
                         ? 'override_base_plus_qty_minus_paid'
                         : ($lastUnpaidOverrideValue !== null ? 'propagate_last' : 'calculate_invoice_unpaid_qty')),
                   'i_qty' => $iQty,
                   'i_paid_timeline' => $iPaid,
                   'current_qbf' => $currentQbf,
                ]);
                $unpaidQtySpecific = $unpaidOpeningForDisplay;
                $unpaidPrevSpecific = $carryAtLoopStart !== null
                   ? (float) $carryAtLoopStart
                   : $lastLoopUnpaid;
               $foundSpecific = true;
            }

            $lastLoopUnpaid = $tempUnpaid;

            // ACUMULAR PARA EL FUTURO
            $historialQty += $iQty;
            $historialPaid += $iPaid;
         }

         // Si por alguna razón el invoice actual no estaba en la lista (caso raro), usamos el override si aplica
         if (!$foundSpecific) {
            $this->logOverrideInvoice('fallback_invoice_not_in_timeline_loop', [
               'invoice_item_id' => $value->getId(),
               'project_item_id' => $project_item_id,
               'current_invoice_id' => $currentInvoiceId,
            ]);
            $currentInv = $this->getDoctrine()->getRepository(Invoice::class)->find($currentInvoiceId);
            $currentInvStart = ($currentInv) ? $currentInv->getStartDate() : null;

            // Si el invoice actual es posterior a la partición unpaid, usar override / cálculo
            $isAfterOverride = ($overridePartitionDate === null) || ($currentInvStart !== null && $currentInvStart >= $overridePartitionDate);

            if ($isAfterOverride && $latestOverride !== null && $anchorUnpaidEffective !== null) {
               // Usar el override como base; QBF siempre: unpaid = max(0, deuda_antes_qbf − QBF)
               $overrideBase = (float) $anchorUnpaidEffective;
               $currentQbf = (float)$value->getQuantityBroughtForward();
               $currentQty = (float)$value->getQuantity();
               $currentPaid = $this->paidQtyOverrideResolver->getEffectivePaidQty($value);
               if (
                  $currentInvStart !== null && $overrideStartDate !== null
                  && $this->isSameCalendarMonth($currentInvStart, $overrideStartDate)
               ) {
                  $unpaidBeforeQbf = $carryIntoCurrentForFallback !== null
                     ? (float) $carryIntoCurrentForFallback
                     : $overrideBase;
               } elseif ($carryIntoCurrentForFallback !== null) {
                  $unpaidBeforeQbf = (float) $carryIntoCurrentForFallback;
               } else {
                  $unpaidBeforeQbf = $overrideBase;
               }
               $unpaidQtySpecific = max(0.0, $unpaidBeforeQbf - $currentQbf);
               $unpaidPrevSpecific = $lastLoopUnpaid;
               $this->logQbf('listar_fallback_override', [
                  'invoice_item_id' => $value->getId(),
                  'project_item_id' => $project_item_id,
                  'override_base' => $overrideBase,
                  'current_qbf' => $currentQbf,
                  'unpaid_before_qbf' => $unpaidBeforeQbf,
                  'unpaid_qty_specific' => $unpaidQtySpecific,
               ]);
               $this->logOverrideInvoice('fallback_override_base', [
                  'invoice_item_id' => $value->getId(),
                  'project_item_id' => $project_item_id,
                  'override_base' => $overrideBase,
                  'current_qty' => $currentQty,
                  'current_paid_effective' => $currentPaid,
                  'current_qbf' => $currentQbf,
                  'unpaid_qty_specific' => $unpaidQtySpecific,
               ]);
            } else {
               // Calcular normalmente
               $unpaidQtySpecific = $this->calculateInvoiceUnpaidQty($historialQty, $historialPaid, $value->getQuantityBroughtForward());
               $unpaidPrevSpecific = $lastLoopUnpaid;
               $this->logOverrideInvoice('fallback_calculate_invoice_unpaid_qty', [
                  'invoice_item_id' => $value->getId(),
                  'project_item_id' => $project_item_id,
                  'historial_qty' => $historialQty,
                  'historial_paid' => $historialPaid,
                  'unpaid_qty_specific' => $unpaidQtySpecific,
               ]);
            }
         }

          // Alinear con ListarItemsParaInvoice solo si hay ancla de unpaid con valor efectivo (misma puerta que
          // la cadena en CalcularUnpaid). Sin ancla, Calcular cae en agregado global (Σ qty − paid de todas las
          // facturas) y reemplazaría mal el timeline en meses anteriores al override (p. ej. agosto cuando la
          // cabecera unpaid es octubre/noviembre — docs/OVERRIDE_PAYMENT_FECHAS_INVOICE.md §2).
          // AHORA CORREGIDO: El ProjectService ahora limita por end_date
          if ($invPeriodStart !== null && $invPeriodEnd !== null
             && $latestOverride !== null && $anchorUnpaidEffective !== null
          ) {
             $alignedUnpaid = (float) $this->projectService->CalcularUnpaidQuantityFromPreviusInvoice(
                (int) $project_item_id,
                $invPeriodStart->format('m/d/Y'),
                $invPeriodEnd->format('m/d/Y'),
                $currentInvoiceId
             );
             // Con exclude_invoice_id la cadena no procesa la línea actual: aligned ≈ deuda antes de QBF de esta
             // línea (como unpaidBeforeQbfForRow en el timeline). Igual que allí: unpaid mostrado = max(0, aligned − QBF).
             $qbfRow = (float) ($value->getQuantityBroughtForward() ?? 0);
             $unpaidQtySpecific = max(0.0, $alignedUnpaid - $qbfRow);
             $unpaidPrevSpecific = $alignedUnpaid;
             $this->logOverrideInvoice('listar_aligned_calcular_unpaid', [
                'invoice_item_id' => $value->getId(),
                'project_item_id' => $project_item_id,
                'exclude_invoice_id' => $currentInvoiceId,
                'aligned_unpaid_before_qbf' => $alignedUnpaid,
                'quantity_brought_forward' => $qbfRow,
                'aligned_unpaid_qty' => $unpaidQtySpecific,
             ]);
          }

          $unpaid_qty = $unpaidQtySpecific;
         // -------------------------------------

         $quantity = $value->getQuantity();
         $quantity_from_previous = $value->getQuantityFromPrevious();
         $quantity_brought_forward = $value->getQuantityBroughtForward();

         $quantity_completed = $quantity + $quantity_from_previous;
         $quantity_final = $quantity + ($quantity_brought_forward ?? 0);

         $total_amount = $quantity_completed * $price;
         $amount_from_previous = $quantity_from_previous * $price;
         $amount_completed = $quantity_completed * $price;
         $amount_final = $quantity_final * $price;
         $amount = $quantity * $price;

         $pd = $this->paidQtyOverrideResolver->resolvePaidQtyDetails($value);
         $paid_qty = (float) ($pd['effective'] ?? 0);
         $this->logOverrideInvoice('item_paid_resolved', [
            'invoice_item_id' => $value->getId(),
            'project_item_id' => $project_item_id,
            'paid_effective' => $paid_qty,
            'paid_base_stored' => $pd['base'] ?? null,
            'override_id' => $pd['override_id'] ?? null,
            'invoice_period' => $pd['invoice_period'] ?? null,
         ]);
         // Si la fila override define paid pero unpaid_qty=NULL en BD, derivar unpaid = quantity_completed - paid (véase InvoiceItemOverridePayment::unpaidQty)
         if (($pd['override_id'] ?? null) !== null
            && $periodOverrideRow !== null
            && $periodOverrideRow->getUnpaidQty() === null
         ) {
            $qbfRow = (float) ($value->getQuantityBroughtForward() ?? 0);
            $unpaid_qty = max(0.0, (float) $quantity_completed - $paid_qty - $qbfRow);
            $this->logQbf('listar_unpaid_derived_completed_minus_paid_minus_qbf', [
               'invoice_item_id' => $value->getId(),
               'quantity_completed' => $quantity_completed,
               'paid_qty' => $paid_qty,
               'qbf' => $qbfRow,
               'unpaid_qty' => $unpaid_qty,
            ]);
            $this->logOverrideInvoice('item_unpaid_derived_completed_minus_paid', [
               'invoice_item_id' => $value->getId(),
               'project_item_id' => $project_item_id,
               'quantity_completed' => $quantity_completed,
               'paid_effective' => $paid_qty,
               'unpaid_qty' => $unpaid_qty,
            ]);
         }
         $unpaid_amount = $unpaid_qty * $price;
         $unpaid_from_previous = $unpaidPrevSpecific;
         $this->logOverrideInvoice('item_line_summary', [
            'invoice_item_id' => $value->getId(),
            'project_item_id' => $project_item_id,
            'unpaid_qty' => $unpaid_qty,
            'unpaid_from_previous' => $unpaid_from_previous,
            'unpaid_amount' => $unpaid_amount,
            'quantity_completed' => $quantity_completed,
         ]);

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
            "amount" => $amount,
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
       
       // Quitar la clave debug para que no rompa el array de items en JavaScript
       $debugInfo = null;
       if (isset($items['_debug'])) {
          $debugInfo = $items['_debug'];
          unset($items['_debug']);
       }

        $this->logOverrideInvoice('listar_items_done', [
           'invoice_id' => $currentInvoiceId,
           'project_id' => $project_id,
           'items_returned' => \count($items),
        ]);

        return $items;
    }

   /**
    * Unpaid qty como en {@see ListarItemsDeInvoice} para la última línea no bond del ítem cuyo invoice tiene
    * start_date estrictamente anterior a $invoiceStartStrictlyBeforeYmd (Y-m-d). Si el cutoff es null, usa el
    * último invoice del ítem. Sirve para Override Payment cuando aún no hay paid/unpaid explícitos en BD.
    */
   public function getUnpaidQtyMatchingInvoiceListarForLastLineBeforeCutoff(int $projectItemId, ?string $invoiceStartStrictlyBeforeYmd): float
   {
      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
      $lines = $invoiceItemRepo->ListarInvoicesDeItem($projectItemId);
      $lastEligible = null;
      foreach ($lines as $ii) {
         $inv = $ii->getInvoice();
         $item = $ii->getProjectItem()?->getItem();
         if ($item !== null && $item->getBond()) {
            continue;
         }
         if ($inv === null || $inv->getStartDate() === null) {
            continue;
         }
         if ($invoiceStartStrictlyBeforeYmd !== null && $invoiceStartStrictlyBeforeYmd !== '') {
            if ($inv->getStartDate()->format('Y-m-d') >= $invoiceStartStrictlyBeforeYmd) {
               continue;
            }
         }
         $lastEligible = $ii;
      }
      if ($lastEligible === null) {
         return 0.0;
      }
      $invoiceId = (int) $lastEligible->getInvoice()->getInvoiceId();
      $rows = $this->ListarItemsDeInvoice($invoiceId);
      foreach ($rows as $row) {
         if ((int) ($row['project_item_id'] ?? 0) === $projectItemId) {
            return (float) ($row['unpaid_qty'] ?? 0);
         }
      }

      return 0.0;
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

         // Asegurar que el ítem Bond del proyecto exista en invoice_item (para Payments)
         $this->AsegurarInvoiceItemBond($entity);

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
         $resultado['invoice_id'] = $entity->getInvoiceId();

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

      // Asegurar que el ítem Bond del proyecto exista en invoice_item (para Payments)
      $this->AsegurarInvoiceItemBond($entity);

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
      $resultado['invoice_id'] = $entity->getInvoiceId();

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
    * AsegurarInvoiceItemBond: Si el proyecto tiene ítem(es) Bond, asegura que exista
    * un InvoiceItem por cada uno en este invoice (aunque no se muestre en la tabla del invoice).
    * Necesario para que en Payments se pueda pagar el Bond como un ítem más.
    *
    * @param Invoice $entity
    * @return void
    */
   private function AsegurarInvoiceItemBond(Invoice $entity): void
   {
      $project = $entity->getProject();
      if ($project === null) {
         return;
      }
      $project_id = $project->getProjectId();
      /** @var ProjectItemRepository $projectItemRepo */
      $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
      $bondProjectItems = $projectItemRepo->ListarBondProjectItems($project_id);
      if (empty($bondProjectItems)) {
         return;
      }
      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
      $em = $this->getDoctrine()->getManager();

      foreach ($bondProjectItems as $bondProjectItem) {
         $existing = $invoiceItemRepo->BuscarItem($entity->getInvoiceId(), $bondProjectItem->getId());
         if ($existing !== null) {
            continue;
         }
         $invoiceItem = new InvoiceItem();
         $invoiceItem->setInvoice($entity);
         $invoiceItem->setProjectItem($bondProjectItem);
         $invoiceItem->setQuantity(0.0);
         $invoiceItem->setQuantityBroughtForward(0.0);
         $invoiceItem->setQuantityFromPrevious(0.0);
         $invoiceItem->setUnpaidFromPrevious(0.0);
         $invoiceItem->setPrice((float) $bondProjectItem->getPrice());
         $invoiceItem->setPaidQty(0.0);
         $invoiceItem->setUnpaidQty(0.0);
         $invoiceItem->setPaidAmount(0.0);
         $invoiceItem->setPaidAmountTotal(0.0);
         $em->persist($invoiceItem);
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

      $qbfPayload = [];
      foreach ($items as $row) {
         $qbfPayload[] = [
            'project_item_id' => $row->project_item_id ?? null,
            'invoice_item_id' => $row->invoice_item_id ?? null,
            'quantity_brought_forward' => $row->quantity_brought_forward ?? null,
         ];
      }
      $this->logQbf('actualizar_unpaid_qbf_start', [
         'current_invoice_id' => (int) $current_invoice_id,
         'project_id' => (int) $project_id,
         'items_qbf' => $qbfPayload,
      ]);

      $this->logOverrideInvoice('actualizar_unpaid_qbf_start', [
         'current_invoice_id' => (int) $current_invoice_id,
         'project_id' => (int) $project_id,
         'items_count' => \count($items),
      ]);

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

      // Para cada item modificado, recalcular unpaid_qty desde el primer invoice
      foreach ($items as $itemData) {
         $project_item_id = $itemData->project_item_id ?? null;
         if ($project_item_id === null || $project_item_id === '') {
            continue;
         }

         $projectItem = $this->getDoctrine()->getRepository(\App\Entity\ProjectItem::class)->find($project_item_id);
         if ($projectItem === null) {
            continue;
         }

         $invPeriodStart = $currentInvoice->getStartDate();
         /** @var ?InvoiceItemOverridePayment $latestOverride ancla unpaid (cabecera ≤ mes invoice), no la misma fila que paid por período */
         $latestOverride = null;
         $anchorUnpaidEffective = null;
         if ($invPeriodStart !== null) {
            $anchorRow = $this->unpaidQtyOverrideResolver->findUnpaidAnchorOverrideRow(
               (int) $project_item_id,
               $invPeriodStart
            );
            if ($anchorRow !== null) {
               $effUnpaid = $this->unpaidQtyOverrideResolver->getEffectiveUnpaidFromOverrideRow($anchorRow);
               if ($effUnpaid !== null) {
                  $latestOverride = $anchorRow;
                  $anchorUnpaidEffective = $effUnpaid;
               }
            }
         }

         $overrideStartDate = null;
         if ($latestOverride !== null) {
            $overrideStartDate = $latestOverride->getOverridePeriodDate();
         }

         $overridePartitionDate = $this->unpaidQtyOverrideResolver->findEarliestUnpaidOverrideHeaderDate((int) $project_item_id);

         $this->logOverrideInvoice('actualizar_unpaid_qbf_project_item', [
            'project_item_id' => (int) $project_item_id,
            'anchor_unpaid_effective' => $anchorUnpaidEffective,
            'override_partition_ymd' => $overridePartitionDate !== null ? $overridePartitionDate->format('Y-m-d') : null,
            'override_start_ymd' => $overrideStartDate !== null ? $overrideStartDate->format('Y-m-d') : null,
            'latest_override_id' => $latestOverride !== null ? $latestOverride->getId() : null,
         ]);

         $allInvoiceItems = $invoiceItemRepo->ListarInvoicesDeItem($project_item_id);
         $invoiceItemMap = [];
         foreach ($allInvoiceItems as $ii) {
            $invoiceItemMap[(int) $ii->getInvoice()->getInvoiceId()] = $ii;
         }

         $historialQty = 0.0;
         $historialPaid = 0.0;
         $seenOverrideIdsQbf = [];
         $lastUnpaidOverrideValue = null;

         for ($i = 0; $i < count($allInvoices); $i++) {
            $inv = $allInvoices[$i];
            $invId = (int)$inv->getInvoiceId();
            $invItem = $invoiceItemMap[$invId] ?? null;
            $invStart = $inv->getStartDate();

            // Solo tocar unpaid desde la primera cabecera con unpaid efectivo (misma partición que ListarItemsDeInvoice)
            $isAfterOverride = ($overridePartitionDate === null) || ($invStart !== null && $invStart >= $overridePartitionDate);

            // Si el invoice es anterior al override, NO modificar su unpaid
            if (!$isAfterOverride && $invItem) {
               // Solo sumar al historial para los siguientes invoices
               $currentQbf = (float)$invItem->getQuantityBroughtForward();
               $iQty = (float)$invItem->getQuantity();
               $iPaid = $this->paidQtyOverrideResolver->paidIncrementForHistorialTimeline($invItem, $seenOverrideIdsQbf);
               $this->logOverrideInvoice('actualizar_unpaid_qbf_skip_before_partition', [
                  'project_item_id' => (int) $project_item_id,
                  'loop_invoice_id' => $invId,
                  'reason' => 'invoice_before_unpaid_partition',
               ]);
               $historialQty += $iQty;
               $historialPaid += $iPaid;
               continue;
            }

            // Datos actuales
            $currentQbf = 0.0;
            $iQty = 0.0;
            $iPaid = 0.0;

            if ($invItem) {
               $currentQbf = ($invId === (int)$current_invoice_id)
                  ? (isset($itemData->quantity_brought_forward) ? (float)$itemData->quantity_brought_forward : 0.0)
                  : (float)$invItem->getQuantityBroughtForward();

               $iQty = (float)$invItem->getQuantity();
               $iPaid = $this->paidQtyOverrideResolver->paidIncrementForHistorialTimeline($invItem, $seenOverrideIdsQbf);
            }

            // Determinar el unpaid: misma regla que ListarItemsDeInvoice (snapshot mes cabecera; encadenar después)
            $nuevoUnpaid = null;
            if ($latestOverride !== null && $anchorUnpaidEffective !== null) {
               $overrideBase = (float) $anchorUnpaidEffective;
               if (
                  $invStart !== null && $overrideStartDate !== null
                  && $this->isSameCalendarMonth($invStart, $overrideStartDate)
               ) {
                  // Mismo mes que override: persistir unpaid = snapshot − QBF; carry sigue con qty − QBF
                  $nuevoUnpaid = max(0.0, $overrideBase - $currentQbf);
                  $lastUnpaidOverrideValue = max(0.0, $overrideBase + $iQty - $currentQbf);
               } else {
                  if ($lastUnpaidOverrideValue !== null) {
                     $carryIn = (float) $lastUnpaidOverrideValue;
                     $nuevoUnpaid = max(0.0, $carryIn - $currentQbf);
                     $lastUnpaidOverrideValue = max(0.0, $carryIn + $iQty - $iPaid - $currentQbf);
                  } else {
                     // Primer invoice después del mes de cabecera: unpaid = snapshot − QBF; carry con qty y paid
                     $nuevoUnpaid = max(0.0, $overrideBase - $currentQbf);
                     $lastUnpaidOverrideValue = max(0.0, $overrideBase + $iQty - $iPaid - $currentQbf);
                  }
               }
            } elseif ($lastUnpaidOverrideValue !== null) {
               $carryIn = (float) $lastUnpaidOverrideValue;
               $nuevoUnpaid = max(0.0, $carryIn - $currentQbf);
               $lastUnpaidOverrideValue = max(0.0, $carryIn + $iQty - $iPaid - $currentQbf);
            } else {
               // Calcular normalmente
               $nuevoUnpaid = $this->calculateInvoiceUnpaidQty($historialQty, $historialPaid, $currentQbf);
            }

            if (abs($currentQbf) > 1e-12 || $invId === (int) $current_invoice_id) {
               $this->logQbf('actualizar_unpaid_qbf_invoice', [
                  'project_item_id' => (int) $project_item_id,
                  'current_invoice_id_being_saved' => (int) $current_invoice_id,
                  'loop_invoice_id' => $invId,
                  'current_qbf' => $currentQbf,
                  'nuevo_unpaid' => $nuevoUnpaid,
                  'last_unpaid_carry' => $lastUnpaidOverrideValue,
               ]);
            }
            $this->logOverrideInvoice('actualizar_unpaid_qbf_invoice', [
               'project_item_id' => (int) $project_item_id,
               'current_invoice_id_being_saved' => (int) $current_invoice_id,
               'loop_invoice_id' => $invId,
               'is_after_override' => $isAfterOverride,
               'nuevo_unpaid' => $nuevoUnpaid,
               'last_unpaid_carry' => $lastUnpaidOverrideValue,
               'i_qty' => $iQty,
               'i_paid' => $iPaid,
               'current_qbf' => $currentQbf,
            ]);

            // Guardar en BD (Si existe el item)
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
    * Cuando en Data T se cambia cantidad o precio de un ítem (o se elimina), se actualiza
    * el/los invoice(s) cuyo periodo contiene esa fecha; los invoices posteriores (#6, #7…)
    * se recalculan en cascada (quantity_from_previous, unpaid, etc.). Los anteriores (#1-#4) no se tocan.
    *
    * - Cantidad (Qty This Period): suma de quantity en Data T para ese project_item en [start_date, end_date].
    * - Precio: promedio ponderado por cantidad en ese periodo; si no hay datos, se mantiene el actual.
    * - Si la cantidad queda en 0 se deja la línea con quantity=0 (no se elimina, para conservar datos de invoices anteriores).
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
         $invoiceId = (int) $invoice->getInvoiceId();
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
            $project_item_id = (int) $project_item_id;
            $invoiceItem = $invoiceItemRepo->BuscarItem($invoiceId, $project_item_id);
            if ($invoiceItem === null) {
               continue;
            }

            $newQuantity = (float) $dataTrackingItemRepo->TotalQuantity('', (string) $project_item_id, $startDate, $endDate);

            $invoiceItem->setQuantity(max(0.0, $newQuantity));
            if ($newQuantity > 0.0) {
               $effectivePrice = $dataTrackingItemRepo->EffectivePriceForPeriod((string) $project_item_id, $startDate, $endDate);
               if ($effectivePrice !== null) {
                  $invoiceItem->setPrice($effectivePrice);
               }
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
         $seenOverrideIdsRecalc = [];
         $lastUnpaidOverrideCarry = null;

         for ($i = 0; $i < count($allInvoices); $i++) {
            $inv = $allInvoices[$i];
            $invId = (int) $inv->getInvoiceId();
            $invItem = $invoiceItemMap[$invId] ?? null;
            $invStart = $inv->getStartDate();

            if ($invItem) {
               $invItem->setQuantityFromPrevious($historialQty);
            }

            $currentQbf = $invItem ? (float) $invItem->getQuantityBroughtForward() : 0.0;
            $iQty = $invItem ? (float) $invItem->getQuantity() : 0.0;
            $iPaid = $invItem
               ? $this->paidQtyOverrideResolver->paidIncrementForHistorialTimeline($invItem, $seenOverrideIdsRecalc)
               : 0.0;

            /** @var ?InvoiceItemOverridePayment $recalcAnchor */
            $recalcAnchor = null;
            $recalcAnchorUnpaidEffective = null;
            if ($invStart !== null) {
               $recalcAnchor = $this->unpaidQtyOverrideResolver->findUnpaidAnchorOverrideRow(
                  (int) $project_item_id,
                  $invStart
               );
               if ($recalcAnchor !== null) {
                  $eff = $this->unpaidQtyOverrideResolver->getEffectiveUnpaidFromOverrideRow($recalcAnchor);
                  if ($eff !== null) {
                     $recalcAnchorUnpaidEffective = $eff;
                  }
               }
            }
            $recalcOverrideStart = $recalcAnchor !== null ? $recalcAnchor->getOverridePeriodDate() : null;

            if ($recalcAnchor !== null && $recalcAnchorUnpaidEffective !== null) {
               $overrideBase = (float) $recalcAnchorUnpaidEffective;
               if (
                  $invStart !== null && $recalcOverrideStart !== null
                  && $this->isSameCalendarMonth($invStart, $recalcOverrideStart)
               ) {
                  $nuevoUnpaid = max(0.0, $overrideBase - $currentQbf);
                  $lastUnpaidOverrideCarry = max(0.0, $overrideBase + $iQty - $currentQbf);
               } else {
                  if ($lastUnpaidOverrideCarry !== null) {
                     $carryIn = (float) $lastUnpaidOverrideCarry;
                     $nuevoUnpaid = max(0.0, $carryIn - $currentQbf);
                     $lastUnpaidOverrideCarry = max(0.0, $carryIn + $iQty - $iPaid - $currentQbf);
                  } else {
                     $nuevoUnpaid = max(0.0, $overrideBase - $currentQbf);
                     $lastUnpaidOverrideCarry = max(0.0, $overrideBase + $iQty - $iPaid - $currentQbf);
                  }
               }
            } else {
               $nuevoUnpaid = $this->calculateInvoiceUnpaidQty($historialQty, $historialPaid, $currentQbf);
               $lastUnpaidOverrideCarry = null;
            }

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
