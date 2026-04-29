<?php

namespace App\Dto\Api\Response\Project\Payload;

/**
 * Fila de items_completion[] en el detalle de proyecto (API app).
 * Origen: {@see \App\Service\Admin\ProjectService::ListarItemsCompletion}.
 */
final readonly class ProjectItemCompletionRowPayload implements \JsonSerializable
{
    public function __construct(
        public mixed $project_item_id,
        public mixed $apply_retainage,
        public int $bonded,
        public int $bond,
        public mixed $item_id,
        public string $item,
        public string $unit,
        public mixed $quantity,
        public mixed $quantity_old,
        public mixed $price,
        public mixed $price_old,
        public mixed $total,
        public mixed $quantity_completed,
        public mixed $amount_completed,
        public mixed $porciento_completion,
        public mixed $invoiced_qty,
        public mixed $total_invoiced_amount,
        public mixed $paid_qty,
        public mixed $total_paid_amount,
        public mixed $diff_qty,
        public mixed $diff_amt,
        public mixed $principal,
        public mixed $change_order,
        public string $change_order_date,
        public mixed $has_quantity_history,
        public mixed $has_price_history,
        public mixed $has_unpaid_qty_history,
        public mixed $has_paid_qty_override_history,
        public int $posicion,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            $row['project_item_id'] ?? null,
            $row['apply_retainage'] ?? null,
            isset($row['bonded']) ? (int) $row['bonded'] : 0,
            isset($row['bond']) ? (int) $row['bond'] : 0,
            $row['item_id'] ?? null,
            (string) ($row['item'] ?? ''),
            (string) ($row['unit'] ?? ''),
            $row['quantity'] ?? null,
            $row['quantity_old'] ?? null,
            $row['price'] ?? null,
            $row['price_old'] ?? null,
            $row['total'] ?? null,
            $row['quantity_completed'] ?? null,
            $row['amount_completed'] ?? null,
            $row['porciento_completion'] ?? null,
            $row['invoiced_qty'] ?? null,
            $row['total_invoiced_amount'] ?? null,
            $row['paid_qty'] ?? null,
            $row['total_paid_amount'] ?? null,
            $row['diff_qty'] ?? null,
            $row['diff_amt'] ?? null,
            $row['principal'] ?? null,
            $row['change_order'] ?? null,
            (string) ($row['change_order_date'] ?? ''),
            $row['has_quantity_history'] ?? null,
            $row['has_price_history'] ?? null,
            $row['has_unpaid_qty_history'] ?? null,
            $row['has_paid_qty_override_history'] ?? null,
            isset($row['posicion']) ? (int) $row['posicion'] : 0,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'project_item_id' => $this->project_item_id,
            'apply_retainage' => $this->apply_retainage,
            'bonded' => $this->bonded,
            'bond' => $this->bond,
            'item_id' => $this->item_id,
            'item' => $this->item,
            'unit' => $this->unit,
            'quantity' => $this->quantity,
            'quantity_old' => $this->quantity_old,
            'price' => $this->price,
            'price_old' => $this->price_old,
            'total' => $this->total,
            'quantity_completed' => $this->quantity_completed,
            'amount_completed' => $this->amount_completed,
            'porciento_completion' => $this->porciento_completion,
            'invoiced_qty' => $this->invoiced_qty,
            'total_invoiced_amount' => $this->total_invoiced_amount,
            'paid_qty' => $this->paid_qty,
            'total_paid_amount' => $this->total_paid_amount,
            'diff_qty' => $this->diff_qty,
            'diff_amt' => $this->diff_amt,
            'principal' => $this->principal,
            'change_order' => $this->change_order,
            'change_order_date' => $this->change_order_date,
            'has_quantity_history' => $this->has_quantity_history,
            'has_price_history' => $this->has_price_history,
            'has_unpaid_qty_history' => $this->has_unpaid_qty_history,
            'has_paid_qty_override_history' => $this->has_paid_qty_override_history,
            'posicion' => $this->posicion,
        ];
    }
}
