<?php

namespace App\Dto\Api\Response\Project\Payload;

/**
 * Fila de invoice_item_override_payment_history[] (API app).
 * Origen: {@see \App\Service\Admin\ProjectService::ListarInvoiceItemOverridePaymentHistoryDeProject}.
 */
final readonly class ProjectPaidQtyOverrideHistoryRowPayload implements \JsonSerializable
{
    public function __construct(
        public mixed $id,
        public string $item_description,
        public string $old_qty,
        public string $new_qty,
        public string $user_name,
        public string $created_at,
        public int $posicion,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            $row['id'] ?? null,
            (string) ($row['item_description'] ?? ''),
            (string) ($row['old_qty'] ?? ''),
            (string) ($row['new_qty'] ?? ''),
            (string) ($row['user_name'] ?? ''),
            (string) ($row['created_at'] ?? ''),
            isset($row['posicion']) ? (int) $row['posicion'] : 0,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'item_description' => $this->item_description,
            'old_qty' => $this->old_qty,
            'new_qty' => $this->new_qty,
            'user_name' => $this->user_name,
            'created_at' => $this->created_at,
            'posicion' => $this->posicion,
        ];
    }
}
