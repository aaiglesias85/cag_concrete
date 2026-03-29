<?php

namespace App\Utils\Admin;

use App\Entity\InvoiceItem;
use App\Entity\InvoiceItemOverridePayment;
use App\Repository\InvoiceItemRepository;
// use App\Utils\OverridePaymentWritelog; // debug override payment (descomentar para trazas)

/**
 * Unpaid qty efectivo: misma fila que {@see InvoicePaidQtyOverrideResolver::selectOverrideRowForInvoicePeriod}
 * (mes del invoice ≤ mes de la cabecera del override; la cabecera más reciente que cumpla).
 * Si esa fila tiene unpaid_qty no null, se usa; si no, el valor en invoice_item.
 *
 * No modifica registros en BD.
 */
class InvoiceUnpaidQtyOverrideResolver
{
   public function __construct(
      private InvoiceItemRepository $invoiceItemRepo,
      private InvoicePaidQtyOverrideResolver $paidPeriodResolver,
   ) {
   }

   public function getEffectiveUnpaidQty(InvoiceItem $invoiceItem): float
   {
      return $this->resolveUnpaidQtyDetails($invoiceItem)['effective'];
   }

   /**
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
   public function resolveUnpaidQtyDetails(InvoiceItem $invoiceItem): array
   {
      $base = (float) ($invoiceItem->getUnpaidQty() ?? 0);
      $invoice = $invoiceItem->getInvoice();
      $pi = $invoiceItem->getProjectItem();
      $invoiceItemId = $invoiceItem->getId();
      $invoiceId = $invoice !== null ? (int) $invoice->getInvoiceId() : null;
      $projectItemId = $pi !== null ? (int) $pi->getId() : null;

      if ($invoice === null || $pi === null) {
         return $this->unpaidQtyDetailsRow($base, $base, null, $invoiceItemId, $invoiceId, $projectItemId, null);
      }
      $invStart = $invoice->getStartDate();
      $invEnd = $invoice->getEndDate();
      if ($invStart === null || $invEnd === null) {
         // OverridePaymentWritelog::writelog("[resolveUnpaidQtyDetails] invoice_item_id={$invoiceItemId} sin fechas invoice -> base unpaid={$base}");
         return $this->unpaidQtyDetailsRow($base, $base, null, $invoiceItemId, $invoiceId, $projectItemId, null);
      }

      // OverridePaymentWritelog::writelog(
      //    "[resolveUnpaidQtyDetails] START invoice_item_id={$invoiceItemId} invoice_id={$invoiceId} project_item_id={$projectItemId} base(unpaid persistido)={$base} period=" . $this->formatInvoicePeriod($invStart, $invEnd)
      // );

      $match = $this->paidPeriodResolver->selectOverrideRowForInvoicePeriod($pi->getId(), $invStart, $invEnd);
      if ($match !== null && $match->getUnpaidQty() !== null) {
         $effective = (float) $match->getUnpaidQty();
         $overrideId = $match->getId() !== null ? (int) $match->getId() : null;
         // OverridePaymentWritelog::writelog(
         //    "[resolveUnpaidQtyDetails] END effective(unpaid override)={$effective} override_id={$overrideId}"
         // );

         return $this->unpaidQtyDetailsRow(
            $effective,
            $base,
            $overrideId,
            $invoiceItemId,
            $invoiceId,
            $projectItemId,
            $this->formatInvoicePeriod($invStart, $invEnd)
         );
      }

      $reason = $match === null ? 'match=null' : 'unpaid_qty en fila override=null';
      // OverridePaymentWritelog::writelog("[resolveUnpaidQtyDetails] END effective=base ({$reason}) base={$base}");

      return $this->unpaidQtyDetailsRow($base, $base, null, $invoiceItemId, $invoiceId, $projectItemId, $this->formatInvoicePeriod($invStart, $invEnd));
   }

   /**
    * @return array{effective: float, base: float, override_id: int|null, invoice_item_id: int|null, invoice_id: int|null, project_item_id: int|null, invoice_period: string|null}
    */
   private function unpaidQtyDetailsRow(
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
    * @param array<int, true> $seenOverrideIds
    */
   public function unpaidIncrementForHistorialTimeline(InvoiceItem $invItem, array &$seenOverrideIds): float
   {
      $details = $this->resolveUnpaidQtyDetails($invItem);
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

   public function sumEffectiveUnpaidQtyForInvoice(int $invoiceId): float
   {
      $items = $this->invoiceItemRepo->ListarItems($invoiceId);
      $sum = 0.0;
      foreach ($items as $ii) {
         $sum += $this->getEffectiveUnpaidQty($ii);
      }

      return $sum;
   }
}
