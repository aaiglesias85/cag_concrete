<?php

namespace App\Utils\Admin;

use App\Entity\InvoiceItem;
use App\Entity\InvoiceItemOverridePayment;
use App\Repository\InvoiceItemOverridePaymentRepository;
use App\Repository\InvoiceItemOverridePaymentUnpaidQtyHistoryRepository;
use App\Repository\InvoiceItemRepository;
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
    * Fecha de cabecera más antigua donde este ítem tiene unpaid efectivo (columna o historial).
    * Particiona la línea de tiempo en InvoiceService: facturas con start estrictamente anteriores usan
    * unpaid calculado por cadena qty/paid (sin snapshot de override); desde esa fecha en adelante aplica
    * ancla/encadenado de override de unpaid.
    */
   public function findEarliestUnpaidOverrideHeaderDate(int $projectItemId): ?\DateTimeInterface
   {
      $rows = $this->overrideRepo->ListarPorProjectItem($projectItemId);
      $best = null;
      foreach ($rows as $o) {
         if (!$o instanceof InvoiceItemOverridePayment) {
            continue;
         }
         if ($this->getEffectiveUnpaidFromOverrideRow($o) === null) {
            continue;
         }
         $hd = $o->getInvoiceOverridePayment()?->getDate();
         if ($hd === null) {
            continue;
         }
         if ($best === null || $hd < $best) {
            $best = $hd;
         }
      }

      return $best;
   }

   /**
    * Fila de override como ancla de unpaid (misma regla que ProjectService / listado invoice).
    */
   public function findUnpaidAnchorOverrideRow(int $projectItemId, \DateTimeInterface $invStart): ?InvoiceItemOverridePayment
   {
      return $this->overrideRepo->findLatestOverrideWithHeaderOnOrBeforeInvoiceMonth($projectItemId, $invStart);
   }

   /**
    * unpaid_qty en BD o último valor en historial de notas.
    */
   public function getEffectiveUnpaidFromOverrideRow(InvoiceItemOverridePayment $row): ?float
   {
      return $this->effectiveUnpaidFromOverrideRow($row);
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
         return $this->unpaidQtyDetailsRow($base, $base, null, $invoiceItemId, $invoiceId, $projectItemId, null);
      }

      $match = $this->overrideRepo->findLatestOverrideWithHeaderOnOrBeforeInvoiceMonth($pi->getId(), $invStart);
      $effectiveOpt = $match !== null ? $this->effectiveUnpaidFromOverrideRow($match) : null;
      if ($match !== null && $effectiveOpt !== null) {
         $effective = $effectiveOpt;
         $overrideId = $match->getId() !== null ? (int) $match->getId() : null;

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
