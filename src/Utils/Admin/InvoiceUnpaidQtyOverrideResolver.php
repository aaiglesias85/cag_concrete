<?php

namespace App\Utils\Admin;

use App\Entity\InvoiceItem;
use App\Entity\InvoiceItemOverridePayment;
use App\Repository\InvoiceItemOverridePaymentRepository;
use App\Repository\InvoiceItemOverridePaymentUnpaidQtyHistoryRepository;
use App\Repository\InvoiceItemRepository;
// use App\Utils\OverridePaymentWritelog; // debug override payment (descomentar para trazas)

/**
 * Unpaid qty efectivo: fila con cabecera más reciente tal que mes(cabecera) ≤ mes(invoice)
 * ({@see InvoiceItemOverridePaymentRepository::findLatestOverrideWithHeaderOnOrBeforeInvoiceMonth}),
 * para que un override siga aplicando en facturas posteriores al mes de la cabecera.
 * Valor: unpaid_qty en la fila o último historial de notas si la columna es null.
 * Si no aplica override, se usa el unpaid persistido en invoice_item.
 *
 * No modifica registros en BD.
 */
class InvoiceUnpaidQtyOverrideResolver
{
   public function __construct(
      private InvoiceItemRepository $invoiceItemRepo,
      private InvoiceItemOverridePaymentRepository $overrideRepo,
      private InvoiceItemOverridePaymentUnpaidQtyHistoryRepository $unpaidHistRepo,
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

      $match = $this->overrideRepo->findLatestOverrideWithHeaderOnOrBeforeInvoiceMonth($pi->getId(), $invStart);
      $effectiveOpt = $match !== null ? $this->effectiveUnpaidFromOverrideRow($match) : null;
      if ($match !== null && $effectiveOpt !== null) {
         $effective = $effectiveOpt;
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

      $reason = $match === null ? 'match=null' : 'sin unpaid efectivo (columna ni historial)';
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

   private function effectiveUnpaidFromOverrideRow(InvoiceItemOverridePayment $row): ?float
   {
      if ($row->getUnpaidQty() !== null) {
         return (float) $row->getUnpaidQty();
      }
      $id = $row->getId();
      if ($id === null) {
         return null;
      }
      $latest = $this->unpaidHistRepo->findLatestByOverrideId((int) $id);
      if ($latest === null) {
         return null;
      }
      $nv = $latest->getNewValue();
      if ($nv === null || $nv === '') {
         return null;
      }

      return (float) $nv;
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
