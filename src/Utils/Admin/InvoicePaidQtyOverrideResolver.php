<?php

namespace App\Utils\Admin;

use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\InvoiceItemOverridePayment;
use App\Repository\InvoiceItemOverridePaymentRepository;
use App\Repository\InvoiceItemRepository;
use App\Repository\InvoiceRepository;

/**
 * Paid qty efectivo para cálculos: si existe un {@see InvoiceItemOverridePayment} aplicable
 * al período del invoice (reglas README_OVERRIDE_PAID_QTY.md), se usa su paid_qty;
 * si no, el valor persistido en invoice_item.
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
      $base = (float) ($invoiceItem->getPaidQty() ?? 0);
      $invoice = $invoiceItem->getInvoice();
      $pi = $invoiceItem->getProjectItem();
      if ($invoice === null || $pi === null) {
         return $base;
      }
      $invStart = $invoice->getStartDate();
      $invEnd = $invoice->getEndDate();
      if ($invStart === null || $invEnd === null) {
         return $base;
      }

      $overrides = $this->overrideRepo->ListarPorProjectItem($pi->getId());
      if ($overrides === []) {
         return $base;
      }

      $match = $this->selectBestOverrideForInvoice($invStart, $invEnd, $overrides);

      return $match !== null ? (float) $match->getPaidQty() : $base;
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

   /**
    * @param InvoiceItemOverridePayment[] $overrides
    */
   private function selectBestOverrideForInvoice(
      \DateTimeInterface $invStart,
      \DateTimeInterface $invEnd,
      array $overrides
   ): ?InvoiceItemOverridePayment {
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
         usort($specificMatches, static fn (InvoiceItemOverridePayment $a, InvoiceItemOverridePayment $b) => ($b->getId() ?? 0) <=> ($a->getId() ?? 0));

         return $specificMatches[0];
      }
      if ($globalMatches !== []) {
         usort($globalMatches, static fn (InvoiceItemOverridePayment $a, InvoiceItemOverridePayment $b) => ($b->getId() ?? 0) <=> ($a->getId() ?? 0));

         return $globalMatches[0];
      }

      return null;
   }

   private function isGlobalOverride(InvoiceItemOverridePayment $o): bool
   {
      return $o->getStartDate() === null && $o->getEndDate() === null;
   }

   private function invoiceOverlapsOverrideRange(
      \DateTimeInterface $invStart,
      \DateTimeInterface $invEnd,
      InvoiceItemOverridePayment $o
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
