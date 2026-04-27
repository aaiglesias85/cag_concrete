<?php

namespace App\Service\Base;

use App\Entity\InvoiceItem;
use App\Entity\InvoiceItemNotes;
use App\Entity\InvoiceItemUnpaidQtyHistory;
use App\Entity\ProjectItemHistory;
use Doctrine\Persistence\ManagerRegistry;

class BaseInvoicePaymentsDisplayService
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
    ) {
    }

    /**
     * Mismo unpaid_qty que muestra la grilla de Payments (no el campo persistido invoice_item.unpaid_qty).
     * quantity_final − paid_qty; bond usa bon_quantity del invoice; notas con override_unpaid_qty (orden DESC).
     *
     * @param array<int, array<string, mixed>>|null $notes si null, se cargan con ListarNotesDeItemInvoice
     */
    public function computeUnpaidQtyForPaymentsDisplay(InvoiceItem $value, ?array $notes = null): float
    {
        $invoice = $value->getInvoice();
        $bon_quantity = $invoice && null !== $invoice->getBonQuantity() ? (float) $invoice->getBonQuantity() : null;

        $is_bond_item = $value->getProjectItem()->getItem()->getBond();

        $quantity = $value->getQuantity();
        $quantity_brought_forward = $value->getQuantityBroughtForward();
        $quantity_final = $quantity + ($quantity_brought_forward ?? 0);

        $paid_qty = $value->getPaidQty();
        $unpaid_qty = max(0.0, $quantity_final - ($paid_qty ?? 0.0));

        if ($is_bond_item) {
            $quantity_final = null !== $bon_quantity ? $bon_quantity : 0.0;
            $unpaid_qty = max(0.0, $quantity_final - ($paid_qty ?? 0.0));
        }

        if (null === $notes) {
            $notes = $this->ListarNotesDeItemInvoice($value->getId());
        }
        if (!$is_bond_item) {
            foreach ($notes as $note) {
                if (isset($note['override_unpaid_qty']) && '' !== (string) $note['override_unpaid_qty']) {
                    $unpaid_qty = (float) $note['override_unpaid_qty'];
                    break;
                }
            }
        }

        return $unpaid_qty;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function ListarPaymentsDeInvoice($invoice_id): array
    {
        $payments = [];

        /** @var \App\Entity\Invoice|null $invoice */
        $invoice = $this->doctrine->getRepository(\App\Entity\Invoice::class)->find($invoice_id);
        $bon_quantity = $invoice && null !== $invoice->getBonQuantity() ? (float) $invoice->getBonQuantity() : null;
        $bon_amount = $invoice && null !== $invoice->getBonAmount() ? (float) $invoice->getBonAmount() : null;

        /** @var \App\Repository\InvoiceItemRepository $invoiceItemRepo */
        $invoiceItemRepo = $this->doctrine->getRepository(InvoiceItem::class);
        $lista = $invoiceItemRepo->ListarItems($invoice_id);
        foreach ($lista as $key => $value) {
            $is_bond_item = $value->getProjectItem()->getItem()->getBond();

            $contract_qty = $value->getProjectItem()->getQuantity();
            $price = $value->getPrice();
            $contract_amount = $contract_qty * $price;

            $quantity_from_previous = $value->getQuantityFromPrevious();
            $unpaid_from_previous = $value->getUnpaidFromPrevious();

            $quantity = $value->getQuantity();
            $quantity_brought_forward = $value->getQuantityBroughtForward();

            $quantity_final = $quantity + ($quantity_brought_forward ?? 0);
            $quantity_completed = ($quantity + $unpaid_from_previous) + $quantity_from_previous;

            $amount = $quantity_final * $price;
            $total_amount = $quantity_completed * $price;

            $paid_qty = $value->getPaidQty();
            $paid_amount = $value->getPaidAmount();
            $paid_amount_total = $value->getPaidAmountTotal();

            if ($is_bond_item) {
                $quantity_final = null !== $bon_quantity ? $bon_quantity : 0.0;
                $amount = null !== $bon_amount ? $bon_amount : 0.0;
                $total_amount = $amount;
            }

            $notes = $this->ListarNotesDeItemInvoice($value->getId());
            $unpaid_qty = $this->computeUnpaidQtyForPaymentsDisplay($value, $notes);

            $project_item_id = $value->getProjectItem()->getId();
            $invoice_item_id = $value->getId();
            /** @var \App\Repository\ProjectItemHistoryRepository $historyRepo */
            $historyRepo = $this->doctrine->getRepository(ProjectItemHistory::class);
            $has_quantity_history = $historyRepo->TieneHistorialCantidad($project_item_id);
            $has_price_history = $historyRepo->TieneHistorialPrecio($project_item_id);
            /** @var \App\Repository\InvoiceItemUnpaidQtyHistoryRepository $unpaidHistoryRepo */
            $unpaidHistoryRepo = $this->doctrine->getRepository(InvoiceItemUnpaidQtyHistory::class);
            $has_unpaid_qty_history = $unpaidHistoryRepo->TieneHistorial($invoice_item_id);

            $payments[] = [
                'invoice_item_id' => $invoice_item_id,
                'project_item_id' => $project_item_id,

                'apply_retainage' => $value->getProjectItem()->getApplyRetainage(),
                'bonded' => $value->getProjectItem()->getBonded() ? 1 : 0,
                'bond' => $is_bond_item ? 1 : 0,
                'paid_qty' => $paid_qty,
                'paid_amount' => $paid_amount,
                'paid_amount_total' => $paid_amount_total,

                'item_id' => $value->getProjectItem()->getItem()->getItemId(),
                'code' => $value->getProjectItem()->getCode(),
                'item' => $value->getProjectItem()->getItem()->getName(),
                'unit' => null != $value->getProjectItem()->getItem()->getUnit() ? $value->getProjectItem()->getItem()->getUnit()->getDescription() : '',
                'contract_qty' => $contract_qty,
                'price' => $price,
                'contract_amount' => $contract_amount,
                'quantity_from_previous' => $quantity_from_previous,
                'unpaid_from_previous' => $unpaid_from_previous,
                'quantity' => $quantity_final,
                'quantity_completed' => $quantity_completed,
                'amount' => $amount,
                'total_amount' => $total_amount,
                'unpaid_qty' => $unpaid_qty,
                'principal' => $value->getProjectItem()->getPrincipal(),
                'change_order' => $value->getProjectItem()->getChangeOrder(),
                'change_order_date' => null != $value->getProjectItem()->getChangeOrderDate() ? $value->getProjectItem()->getChangeOrderDate()->format('m/d/Y') : '',
                'has_quantity_history' => $has_quantity_history,
                'has_price_history' => $has_price_history,
                'has_unpaid_qty_history' => $has_unpaid_qty_history,
                'notes' => $notes,
                'posicion' => $key,
                'is_closed_manual' => $value->getIsClosedManual() ? 1 : 0,
            ];
        }

        return $payments;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function ListarNotesDeItemInvoice($invoice_item_id): array
    {
        $notes = [];

        /** @var \App\Repository\InvoiceItemNotesRepository $invoiceItemNotesRepo */
        $invoiceItemNotesRepo = $this->doctrine->getRepository(InvoiceItemNotes::class);
        $lista = $invoiceItemNotesRepo->ListarNotesDeItemInvoice($invoice_item_id);
        foreach ($lista as $key => $value) {
            $note = $value->getNotes();
            $note = mb_convert_encoding($note, 'UTF-8', 'UTF-8');

            $notes[] = [
                'id' => $value->getId(),
                'notes' => $note,
                'date' => $value->getDate()->format('m/d/Y'),
                'override_unpaid_qty' => $value->getOverrideUnpaidQty(),
                'posicion' => $key,
            ];
        }

        return $notes;
    }
}
