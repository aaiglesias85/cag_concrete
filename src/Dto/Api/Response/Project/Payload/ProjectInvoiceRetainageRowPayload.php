<?php

namespace App\Dto\Api\Response\Project\Payload;

/**
 * Fila de invoices_retainage[] en el detalle de proyecto (API app).
 * Origen: {@see \App\Service\Admin\ProjectService::ListarInvoicesConRetainage}.
 */
final readonly class ProjectInvoiceRetainageRowPayload implements \JsonSerializable
{
    public function __construct(
        public mixed $invoice_id,
        public mixed $invoice_number,
        public string $invoice_date,
        public mixed $invoice_amount,
        public mixed $paid_amount,
        public int $paid,
        public mixed $retainage_percentage,
        public mixed $inv_ret_amt,
        public mixed $retainage_amount,
        public mixed $paid_ret_amt,
        public mixed $total_retainage_to_date,
        public string $ajuste_retainage,
        public int $retainage_reimbursed,
        public mixed $reimbursed_amount,
        public string $startDate,
        public string $endDate,
        public string $reimbursed_date,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            $row['invoice_id'] ?? null,
            $row['invoice_number'] ?? '',
            (string) ($row['invoice_date'] ?? ''),
            $row['invoice_amount'] ?? null,
            $row['paid_amount'] ?? null,
            isset($row['paid']) ? (int) $row['paid'] : 0,
            $row['retainage_percentage'] ?? null,
            $row['inv_ret_amt'] ?? null,
            $row['retainage_amount'] ?? null,
            $row['paid_ret_amt'] ?? null,
            $row['total_retainage_to_date'] ?? null,
            (string) ($row['ajuste_retainage'] ?? ''),
            isset($row['retainage_reimbursed']) ? (int) $row['retainage_reimbursed'] : 0,
            $row['reimbursed_amount'] ?? null,
            (string) ($row['startDate'] ?? ''),
            (string) ($row['endDate'] ?? ''),
            (string) ($row['reimbursed_date'] ?? ''),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'invoice_id' => $this->invoice_id,
            'invoice_number' => $this->invoice_number,
            'invoice_date' => $this->invoice_date,
            'invoice_amount' => $this->invoice_amount,
            'paid_amount' => $this->paid_amount,
            'paid' => $this->paid,
            'retainage_percentage' => $this->retainage_percentage,
            'inv_ret_amt' => $this->inv_ret_amt,
            'retainage_amount' => $this->retainage_amount,
            'paid_ret_amt' => $this->paid_ret_amt,
            'total_retainage_to_date' => $this->total_retainage_to_date,
            'ajuste_retainage' => $this->ajuste_retainage,
            'retainage_reimbursed' => $this->retainage_reimbursed,
            'reimbursed_amount' => $this->reimbursed_amount,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'reimbursed_date' => $this->reimbursed_date,
        ];
    }
}
