<?php

namespace App\Utils\Admin;

use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\InvoiceItemOverridePayment;
use App\Repository\InvoiceItemOverridePaymentRepository;
use App\Repository\InvoiceItemRepository;
use App\Repository\InvoiceRepository;
use App\Utils\OverridePaymentWritelog;

/**
 * Paid qty efectivo para cálculos: si existe un {@see InvoiceItemOverridePayment} aplicable
 * al invoice, se usa su paid_qty; si no, el valor persistido en invoice_item.
 *
 * Modelo de datos: la fecha de período vive en la cabecera {@see InvoiceOverridePayment} (`date`);
 * cada línea `invoice_item_override_payment` enlaza a esa cabecera (`invoice_override_payment_id`).
 * Coincidencia: el mes del invoice (start) debe ser ≤ al mes de la cabecera del override; entre varias,
 * la cabecera más reciente que cumpla (override cubre ese mes y los anteriores; paid_qty = acumulado).
 * En negocio la cabecera suele tener `date` informada.
 *
 * No modifica registros en BD.
 */
class InvoicePaidQtyOverrideResolver
{
   public function __construct(
      private InvoiceItemOverridePaymentRepository $overrideRepo,
      private InvoiceItemRepository $invoiceItemRepo,
      private InvoiceRepository $invoiceRepo
   ) {
   }

   public function getEffectivePaidQty(InvoiceItem $invoiceItem): float
   {
      return $this->resolvePaidQtyDetails($invoiceItem)['effective'];
   }

   /**
    * Fila aplicable al invoice: mes(invoice.start) ≤ mes(cabecera); entre candidatas, cabecera más reciente
    * ({@see InvoiceItemOverridePaymentRepository::findLatestNullStartForInvoicePeriodAfterEndDate}).
    */
   public function selectOverrideRowForInvoicePeriod(int $projectItemId, \DateTimeInterface $invStart, \DateTimeInterface $invEnd): ?InvoiceItemOverridePayment
   {
      $is = $invStart->format('Y-m-d');
      $ie = $invEnd->format('Y-m-d');
      OverridePaymentWritelog::writelog(
         "[selectOverrideRowForInvoicePeriod] projectItemId={$projectItemId} invStart={$is} invEnd={$ie}"
      );
      $row = $this->overrideRepo->findLatestNullStartForInvoicePeriodAfterEndDate($projectItemId, $invStart, $invEnd);
      if ($row !== null) {
         $rid = (int) ($row->getId() ?? 0);
         OverridePaymentWritelog::writelog("[selectOverrideRowForInvoicePeriod] fila id={$rid} paid_qty=" . ($row->getPaidQty() ?? 'null'));
      } else {
         OverridePaymentWritelog::writelog('[selectOverrideRowForInvoicePeriod] fila=null');
      }

      return $row;
   }

   /**
    * Misma lógica que getEffectivePaidQty, con metadatos para trazas y depuración.
    *
    * @return array{
    *   effective: float,
    *   base: float,
    *   override_id: int|null,
    *   invoice_item_id: int|null,
    *   invoice_id: int|null,
    *   project_item_id: int|null,
    *   invoice_period: string|null
    * }
    */
   public function resolvePaidQtyDetails(InvoiceItem $invoiceItem): array
   {
      $base = (float) ($invoiceItem->getPaidQty() ?? 0);
      $invoice = $invoiceItem->getInvoice();
      $pi = $invoiceItem->getProjectItem();
      $invoiceItemId = $invoiceItem->getId();
      $invoiceId = $invoice !== null ? (int) $invoice->getInvoiceId() : null;
      $projectItemId = $pi !== null ? (int) $pi->getId() : null;

      OverridePaymentWritelog::writelog(
         "[resolvePaidQtyDetails] START invoice_item_id={$invoiceItemId} invoice_id={$invoiceId} project_item_id={$projectItemId} base(persistido)={$base}"
      );

      if ($invoice === null || $pi === null) {
         OverridePaymentWritelog::writelog('[resolvePaidQtyDetails] sin invoice o projectItem -> effective=base');
         return $this->paidQtyDetailsRow($base, $base, null, $invoiceItemId, $invoiceId, $projectItemId, null);
      }
      $invStart = $invoice->getStartDate();
      $invEnd = $invoice->getEndDate();
      if ($invStart === null || $invEnd === null) {
         OverridePaymentWritelog::writelog('[resolvePaidQtyDetails] invoice sin start/end date -> effective=base');
         return $this->paidQtyDetailsRow($base, $base, null, $invoiceItemId, $invoiceId, $projectItemId, null);
      }

      $invStartYmd = $invStart->format('Y-m-d');
      $invEndYmd = $invEnd->format('Y-m-d');
      OverridePaymentWritelog::writelog(
         "[resolvePaidQtyDetails] invoice_id={$invoiceId} invStartYmd={$invStartYmd} invEndYmd={$invEndYmd} "
         . 'REGLA: mes(invoice) <= mes(cabecera); si hay varias, cabecera más reciente.'
      );

      $overrides = $this->overrideRepo->ListarPorProjectItem($pi->getId());
      if ($overrides === []) {
         OverridePaymentWritelog::writelog(
            "[resolvePaidQtyDetails] project_item_id={$projectItemId} sin filas en invoice_item_override_payment -> effective=base (paid persistido)"
         );
         return $this->paidQtyDetailsRow($base, $base, null, $invoiceItemId, $invoiceId, $projectItemId, $this->formatInvoicePeriod($invStart, $invEnd));
      }

      $this->logOverrideRowsVsInvoiceStart($invoiceItemId, $invoiceId, $invStartYmd, $overrides);

      $match = $this->selectOverrideRowForInvoicePeriod((int) $pi->getId(), $invStart, $invEnd);
      $ovPaid = $match !== null ? $match->getPaidQty() : null;
      $effective = $match !== null && $ovPaid !== null ? (float) $ovPaid : $base;
      $overrideId = null;
      if ($match !== null && $match->getId() !== null) {
         $overrideId = (int) $match->getId();
      }
      $winnerHeaderYmd = '—';
      if ($match !== null) {
         $wh = $match->getInvoiceOverridePayment()?->getDate();
         $winnerHeaderYmd = $wh !== null ? $wh->format('Y-m-d') : 'null';
      }

      OverridePaymentWritelog::writelog(
         "[resolvePaidQtyDetails] END invoice_id={$invoiceId} project_item_id={$projectItemId} effective={$effective} base={$base} "
         . 'override_line_id=' . ($overrideId ?? 'null') . " winner_headerYmd={$winnerHeaderYmd} invStartYmd={$invStartYmd} period=" . $this->formatInvoicePeriod($invStart, $invEnd)
      );

      return $this->paidQtyDetailsRow(
         $effective,
         $base,
         $overrideId,
         $invoiceItemId,
         $invoiceId,
         $projectItemId,
         $this->formatInvoicePeriod($invStart, $invEnd)
      );
   }

   /**
    * @return array{effective: float, base: float, override_id: int|null, invoice_item_id: int|null, invoice_id: int|null, project_item_id: int|null, invoice_period: string|null}
    */
   private function paidQtyDetailsRow(
      float $effective,
      float $base,
      ?int $overrideId,
      ?int $invoiceItemId,
      ?int $invoiceId,
      ?int $projectItemId,
      ?string $invoicePeriod
   ): array {
      return [
         'effective' => $effective,
         'base' => $base,
         'override_id' => $overrideId,
         'invoice_item_id' => $invoiceItemId,
         'invoice_id' => $invoiceId,
         'project_item_id' => $projectItemId,
         'invoice_period' => $invoicePeriod,
      ];
   }

   private function formatInvoicePeriod(\DateTimeInterface $invStart, \DateTimeInterface $invEnd): string
   {
      return $invStart->format('Y-m-d') . '..' . $invEnd->format('Y-m-d');
   }

   /**
    * Diagnóstico: por cada fila override del ítem, fecha de cabecera vs invStart y si entra en la regla.
    *
    * @param InvoiceItemOverridePayment[] $overrides
    */
   private function logOverrideRowsVsInvoiceStart(int $projectItemId, ?int $invoiceId, string $invStartYmd, array $overrides): void
   {
      foreach ($overrides as $o) {
         if (!$o instanceof InvoiceItemOverridePayment) {
            continue;
         }
         $oid = (int) ($o->getId() ?? 0);
         $pq = $o->getPaidQty();
         $hd = $o->getInvoiceOverridePayment()?->getDate();
         $headerId = $o->getInvoiceOverridePayment()?->getInvoiceOverridePaymentId();
         if ($hd === null) {
            OverridePaymentWritelog::writelog(
               "[overrideVsInvoice] invoice_id={$invoiceId} project_item_id={$projectItemId} line_override_id={$oid} "
               . "cabecera_id=" . ($headerId ?? 'null') . " headerDate=NULL -> EXCLUIDA (sin fecha en cabecera)"
            );
            continue;
         }
         $hdYmd = $hd->format('Y-m-d');
         if ($hdYmd > $invStartYmd) {
            OverridePaymentWritelog::writelog(
               "[overrideVsInvoice] invoice_id={$invoiceId} project_item_id={$projectItemId} line_override_id={$oid} "
               . "cabecera_id={$headerId} headerYmd={$hdYmd} paid_qty={$pq} -> NO_APLICA (headerYmd > invStartYmd {$invStartYmd}; override posterior al inicio del invoice)"
            );
            continue;
         }
         OverridePaymentWritelog::writelog(
            "[overrideVsInvoice] invoice_id={$invoiceId} project_item_id={$projectItemId} line_override_id={$oid} "
            . "cabecera_id={$headerId} headerYmd={$hdYmd} paid_qty={$pq} -> CANDIDATA (headerYmd<=invStartYmd); el repositorio elige la cabecera más reciente entre candidatas"
         );
      }
   }

   /**
    * Incremento de paid acumulado en timeline (misma regla que ProjectService::computePreviousInvoiceTotalsForProjectItem):
    * cada `invoice_item_override_payment` (override_id) cuenta una sola vez; no se multiplica por número de facturas.
    * Líneas sin override: suma el paid_qty almacenado en la línea.
    *
    * @param array<int, true> $seenOverrideIds Modificado por referencia; reiniciar por cada project_item.
    */
   public function paidIncrementForHistorialTimeline(InvoiceItem $invItem, array &$seenOverrideIds): float
   {
      $details = $this->resolvePaidQtyDetails($invItem);
      $oid = $details['override_id'];
      if ($oid !== null) {
         if (isset($seenOverrideIds[$oid])) {
            return 0.0;
         }
         $seenOverrideIds[$oid] = true;

         return (float) $details['effective'];
      }

      return (float) $details['base'];
   }

   /**
    * Suma de paid_qty efectivos de líneas Bond (item.bond) en un invoice.
    * Sustituye a {@see InvoiceItemRepository::SumBondPaidQtyForInvoice} cuando interviene override.
    */
   public function sumEffectiveBondPaidQtyForInvoice(int $invoiceId): float
   {
      $items = $this->invoiceItemRepo->ListarItems($invoiceId);
      $sum = 0.0;
      foreach ($items as $ii) {
         $item = $ii->getProjectItem()?->getItem();
         if ($item === null || !$item->getBond()) {
            continue;
         }
         $sum += $this->getEffectivePaidQty($ii);
      }

      return $sum;
   }

   /**
    * Suma de paid_qty efectivos Bond en invoices del proyecto con start_date <= fecha dada (m/d/Y).
    */
   public function sumEffectiveBondPaidQtyForProjectBeforeOrOnDate(int $projectId, string $startDateMdy): float
   {
      $date = \DateTime::createFromFormat('m/d/Y', trim($startDateMdy));
      if (!$date) {
         return 0.0;
      }
      $cutoff = $date->format('Y-m-d');

      $invoices = $this->invoiceRepo->ListarInvoicesRangoFecha('', (string) $projectId, '', '', '');
      $sum = 0.0;
      foreach ($invoices as $invoice) {
         /** @var Invoice $invoice */
         $sd = $invoice->getStartDate();
         if ($sd === null) {
            continue;
         }
         if ($sd->format('Y-m-d') > $cutoff) {
            continue;
         }
         $sum += $this->sumEffectiveBondPaidQtyForInvoice((int) $invoice->getInvoiceId());
      }

      return $sum;
   }

}
