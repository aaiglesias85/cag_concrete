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
use App\Repository\ProjectRepository;
use App\Utils\Base;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Border;

class InvoiceService extends Base
{
   private function sortInvoicesByStartDateAndId(array &$invoices): void
   {
      usort($invoices, function ($a, $b) {
         $dateCompare = $a->getStartDate() <=> $b->getStartDate();
         if ($dateCompare !== 0) {
            return $dateCompare;
         }
         return $a->getInvoiceId() <=> $b->getInvoiceId();
      });
   }

   private function buildInvoiceItemSeriesForInvoices(array $allInvoices, array $invoiceItemMap, ?int $overrideInvoiceId = null, ?float $overrideQbf = null): array
   {
      $qbf = [];
      $prefixQty = [0.0];
      $prefixPaid = [0.0];
      $prefixQbf = [0.0];

      foreach ($allInvoices as $idx => $invoice) {
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
    * REGLA CORREGIDA: Comparación LOCAL (Pago Invoice actual vs QBF Invoice actual)
    */
   private function calculateInvoiceUnpaidQty(float $sumPrevQty, float $sumPrevPaidQty, float $sumPrevQbf, float $currentQbf, float $currentPaid): float
   {
      // Regla 1: Sin pagos previos
      if ($sumPrevPaidQty <= 0) {
         return max(0, $sumPrevQty - $currentQbf);
      }

      // Regla 2: Con pagos
      if ($currentPaid > $currentQbf) {
         // Si el pago local supera al QBF local, SI sumamos el historial
         return max(0, ($sumPrevQty + $sumPrevQbf) - $sumPrevPaidQty);
      }

      // --- AQUÍ ESTABA EL ERROR ---
      // Si NO supera (tu caso de 0 pago vs 30 QBF): 
      // "Se resta solo QBF de él NO de los otros"
      // Calculamos la deuda base (Cantidad - Pagos) y restamos SOLO el QBF actual.
      $baseDebtPrev = max(0, $sumPrevQty - $sumPrevPaidQty);

      return max(0, $baseDebtPrev - $currentQbf);
   }

   public function ListarItemsDeInvoice($invoice_id)
   {
      $items = [];

      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
      $lista = $invoiceItemRepo->ListarItems($invoice_id);

      // Obtener el invoice actual
      $currentInvoice = $this->getDoctrine()->getRepository(Invoice::class)->find($invoice_id);
      if (!$currentInvoice) {
         return $items;
      }
      $currentInvoiceId = $currentInvoice->getInvoiceId();

      // Obtener todos los invoices del proyecto una sola vez
      $project_id = $currentInvoice->getProject()->getProjectId();
      /** @var InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
      $allInvoices = $invoiceRepo->ListarInvoicesRangoFecha('', $project_id, '', '', '');
      $this->sortInvoicesByStartDateAndId($allInvoices);

      $invoiceIndexById = [];
      foreach ($allInvoices as $idx => $invoice) {
         $invoiceIndexById[(int) $invoice->getInvoiceId()] = (int) $idx;
      }

      foreach ($lista as $key => $value) {
         $contract_qty = $value->getProjectItem()->getQuantity();
         $price = $value->getPrice();
         $contract_amount = $contract_qty * $price;
         $quantity_from_previous = $value->getQuantityFromPrevious();
         $project_item_id = $value->getProjectItem()->getId();

         // Mapa para series
         $allInvoiceItems = $invoiceItemRepo->ListarInvoicesDeItem($project_item_id);
         $invoiceItemMap = [];
         foreach ($allInvoiceItems as $invoiceItem) {
            $inv_id = $invoiceItem->getInvoice()->getInvoiceId();
            $invoiceItemMap[$inv_id] = $invoiceItem;
         }

         $currentIndex = $invoiceIndexById[(int) $currentInvoiceId] ?? -1;
         $series = $this->buildInvoiceItemSeriesForInvoices($allInvoices, $invoiceItemMap);

         $sumPrevItemQty = (float) ($series['prefixQty'][$currentIndex] ?? 0);
         $sumPrevPaidQty = (float) ($series['prefixPaid'][$currentIndex] ?? 0);
         $qbfTotalPrev = (float) ($series['prefixQbf'][$currentIndex] ?? 0);

         // 1. CÁLCULO DE PAGO LOCAL
         $current_paid_val = (float) (($series['prefixPaid'][$currentIndex + 1] ?? $sumPrevPaidQty) - $sumPrevPaidQty);
         $current_qbf = (float) ($value->getQuantityBroughtForward() ?? 0);

         // 2. LLAMADA CON COMPARACIÓN LOCAL
         $unpaid_qty = $this->calculateInvoiceUnpaidQty($sumPrevItemQty, $sumPrevPaidQty, $qbfTotalPrev, $current_qbf, $current_paid_val);

         $quantity = $value->getQuantity();
         $quantity_brought_forward = $value->getQuantityBroughtForward();
         $quantity_completed = $quantity + $quantity_from_previous;

         $amount = $quantity * $price;
         $total_amount = $quantity_completed * $price;
         $amount_from_previous = $quantity_from_previous * $price;
         $amount_completed = $quantity_completed * $price;
         $paid_qty = $value->getPaidQty();

         $unpaid_from_previous = $unpaid_qty;
         $unpaid_amount = $unpaid_qty * $price;

         $quantity_final = $quantity + $quantity_brought_forward;
         $amount_final = $quantity_final * $price;

         /** @var ProjectItemHistoryRepository $historyRepo */
         $historyRepo = $this->getDoctrine()->getRepository(ProjectItemHistory::class);
         $has_quantity_history = $historyRepo->TieneHistorialCantidad($project_item_id);
         $has_price_history = $historyRepo->TieneHistorialPrecio($project_item_id);

         $items[] = [
            "invoice_item_id" => $value->getId(),
            "project_item_id" => $project_item_id,
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
            "unpaid_qty" => $unpaid_qty, // Valor recalculado
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

      return $items;
   }

   private function ActualizarUnpaidQtyPorQuantityBroughtForward($currentInvoice, $items)
   {
      $project_id = $currentInvoice->getProject()->getProjectId();
      $allInvoices = $this->getDoctrine()->getRepository(Invoice::class)->ListarInvoicesRangoFecha('', $project_id, '', '', '');
      $this->sortInvoicesByStartDateAndId($allInvoices);

      $invoiceIndexById = [];
      foreach ($allInvoices as $idx => $inv) {
         $invoiceIndexById[(int) $inv->getInvoiceId()] = (int) $idx;
      }

      foreach ($items as $itemData) {
         $project_item_id = $itemData->project_item_id ?? null;
         if (!$project_item_id) continue;

         $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
         $allInvoiceItems = $invoiceItemRepo->ListarInvoicesDeItem($project_item_id);
         $invoiceItemMap = [];
         foreach ($allInvoiceItems as $it) {
            $invoiceItemMap[$it->getInvoice()->getInvoiceId()] = $it;
         }

         $startIndex = $invoiceIndexById[(int) $currentInvoice->getInvoiceId()] ?? -1;
         if ($startIndex === -1) continue;

         $overrideQbf = isset($itemData->quantity_brought_forward) ? (float) $itemData->quantity_brought_forward : 0.0;
         $series = $this->buildInvoiceItemSeriesForInvoices($allInvoices, $invoiceItemMap, (int) $currentInvoice->getInvoiceId(), $overrideQbf);

         for ($i = $startIndex; $i < count($allInvoices); $i++) {
            $invoice_id = (int) $allInvoices[$i]->getInvoiceId();
            $invoice_item = $invoiceItemMap[$invoice_id] ?? null;
            if (!$invoice_item) continue;

            $sumPrevItemQty = (float) ($series['prefixQty'][$i] ?? 0);
            $sumPrevPaidQty = (float) ($series['prefixPaid'][$i] ?? 0);
            $qbfTotalPrev = (float) ($series['prefixQbf'][$i] ?? 0);
            $current_qbf = (float) ($series['qbf'][$i] ?? 0);

            // Pago LOCAL para el recalculo
            $current_paid = (float) (($series['prefixPaid'][$i + 1] ?? $sumPrevPaidQty) - $sumPrevPaidQty);

            $unpaid_qty = $this->calculateInvoiceUnpaidQty($sumPrevItemQty, $sumPrevPaidQty, $qbfTotalPrev, $current_qbf, $current_paid);

            $invoice_item->setUnpaidQty($unpaid_qty);
            $invoice_item->setUnpaidFromPrevious($unpaid_qty);
         }
      }
   }

   // ... (Resto de funciones: SalvarInvoice, ExportarExcel, etc., irían aquí sin duplicarse)
}
