<?php

namespace App\Utils\Admin;

use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\InvoiceItemOverrideUnpaidQty;
use App\Repository\InvoiceItemOverrideUnpaidQtyRepository;
use App\Repository\InvoiceItemRepository;
use App\Repository\InvoiceRepository;

/**
 * Unpaid qty efectivo para cálculos: si existe un {@see InvoiceItemOverrideUnpaidQty} aplicable
 * al período del invoice (reglas similares a Paid Override), se usa su unpaid_qty;
 * si no, el valor persistido en invoice_item.
 *
 * No modifica registros en BD.
 */
class InvoiceUnpaidQtyOverrideResolver
{
   public function __construct(
      private InvoiceItemOverrideUnpaidQtyRepository $overrideRepo,
      private InvoiceItemRepository $invoiceItemRepo,
      private InvoiceRepository $invoiceRepo
   ) {
   }

   public function getEffectiveUnpaidQty(InvoiceItem $invoiceItem): float
   {
      return $this->resolveUnpaidQtyDetails($invoiceItem)['effective'];
   }

   /**
    * Misma lógica que getEffectiveUnpaidQty, con metadatos para trazas y depuración.
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

      $overrides = $this->overrideRepo->ListarPorProjectItem($pi->getId());
      if ($overrides === []) {
         return $this->unpaidQtyDetailsRow($base, $base, null, $invoiceItemId, $invoiceId, $projectItemId, $this->formatInvoicePeriod($invStart, $invEnd));
      }

      $match = $this->selectBestOverrideForInvoice($invStart, $invEnd, $overrides);
      $effective = $match !== null ? (float) $match->getUnpaidQty() : $base;
      $overrideId = null;
      if ($match !== null && $match->getId() !== null) {
         $overrideId = (int) $match->getId();
      }

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
    * Incremento de unpaid acumulado en timeline (misma regla que getEffectiveUnpaidQty):
    * cada override_id cuenta una sola vez.
    *
    * @param array<int, true> $seenOverrideIds Modificado por referencia; reiniciar por cada project_item.
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

   /**
    * Suma de unpaid_qty efectivos de líneas en un invoice.
    */
   public function sumEffectiveUnpaidQtyForInvoice(int $invoiceId): float
   {
      $items = $this->invoiceItemRepo->ListarItems($invoiceId);
      $sum = 0.0;
      foreach ($items as $ii) {
         $sum += $this->getEffectiveUnpaidQty($ii);
      }

      return $sum;
   }

   /**
    * @param InvoiceItemOverrideUnpaidQty[] $overrides
    */
   private function selectBestOverrideForInvoice(
      \DateTimeInterface $invStart,
      \DateTimeInterface $invEnd,
      array $overrides
   ): ?InvoiceItemOverrideUnpaidQty {
      $specificMatches = [];
      $globalMatches = [];

      foreach ($overrides as $o) {
         if ($this->isGlobalOverride($o)) {
            $globalMatches[] = $o;
            continue;
         }
         if ($this->invoiceOverlapsOverrideRange($invStart, $invEnd, $o)) {
            $specificMatches[] = $o;
         }
      }

      if ($specificMatches !== []) {
         usort($specificMatches, static fn (InvoiceItemOverrideUnpaidQty $a, InvoiceItemOverrideUnpaidQty $b) => ($b->getId() ?? 0) <=> ($a->getId() ?? 0));

         return $specificMatches[0];
      }
      if ($globalMatches !== []) {
         usort($globalMatches, static fn (InvoiceItemOverrideUnpaidQty $a, InvoiceItemOverrideUnpaidQty $b) => ($b->getId() ?? 0) <=> ($a->getId() ?? 0));

         return $globalMatches[0];
      }

      return null;
   }

   private function isGlobalOverride(InvoiceItemOverrideUnpaidQty $o): bool
   {
      return $o->getStartDate() === null && $o->getEndDate() === null;
   }

   private function invoiceOverlapsOverrideRange(
      \DateTimeInterface $invStart,
      \DateTimeInterface $invEnd,
      InvoiceItemOverrideUnpaidQty $o
   ): bool {
      $os = $o->getStartDate();
      $oe = $o->getEndDate();
      if ($os === null && $oe === null) {
         return false;
      }

      $is = $invStart->format('Y-m-d');
      $ie = $invEnd->format('Y-m-d');
      $rangeStart = $os !== null ? $os->format('Y-m-d') : '0000-01-01';
      $rangeEnd = $oe !== null ? $oe->format('Y-m-d') : '9999-12-31';

      return $is <= $rangeEnd && $ie >= $rangeStart;
   }
}
