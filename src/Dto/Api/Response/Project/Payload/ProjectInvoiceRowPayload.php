<?php

namespace App\Dto\Api\Response\Project\Payload;

/**
 * Fila de invoices[] en el detalle de proyecto (API app).
 * Origen: {@see \App\Service\Admin\ProjectService::ListarInvoicesDeProject}.
 */
final readonly class ProjectInvoiceRowPayload implements \JsonSerializable
{
    public function __construct(
        public mixed $invoice_id,
        public mixed $number,
        public string $company,
        public string $project,
        public string $startDate,
        public string $endDate,
        public string $notes,
        public string $total,
        public string $createdAt,
        public int $paid,
        public int $posicion,
        public bool $hasOverride,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        $hasOverride = $row['hasOverride'] ?? false;

        return new self(
            $row['invoice_id'] ?? null,
            $row['number'] ?? '',
            (string) ($row['company'] ?? ''),
            (string) ($row['project'] ?? ''),
            (string) ($row['startDate'] ?? ''),
            (string) ($row['endDate'] ?? ''),
            (string) ($row['notes'] ?? ''),
            (string) ($row['total'] ?? ''),
            (string) ($row['createdAt'] ?? ''),
            isset($row['paid']) ? (int) $row['paid'] : 0,
            isset($row['posicion']) ? (int) $row['posicion'] : 0,
            (bool) $hasOverride,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'invoice_id' => $this->invoice_id,
            'number' => $this->number,
            'company' => $this->company,
            'project' => $this->project,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'notes' => $this->notes,
            'total' => $this->total,
            'createdAt' => $this->createdAt,
            'paid' => $this->paid,
            'posicion' => $this->posicion,
            'hasOverride' => $this->hasOverride,
        ];
    }
}
