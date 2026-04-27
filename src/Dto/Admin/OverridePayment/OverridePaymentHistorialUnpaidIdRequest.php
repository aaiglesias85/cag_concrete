<?php

namespace App\Dto\Admin\OverridePayment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/** Acepta `invoice_item_override_payment_id` o `invoice_item_override_unpaid_qty_id`. */
final class OverridePaymentHistorialUnpaidIdRequest
{
    #[Assert\NotBlank(message: 'id is required.')]
    #[Assert\Positive]
    public ?int $id = null;

    public static function fromHttpRequest(Request $request): self
    {
        $dto = new self();
        $pid = $request->get('invoice_item_override_payment_id');
        if (null === $pid || '' === (string) $pid) {
            $pid = $request->get('invoice_item_override_unpaid_qty_id');
        }
        $dto->id = self::positiveIntOrNull($pid);

        return $dto;
    }

    private static function positiveIntOrNull(mixed $v): ?int
    {
        if (null === $v || false === $v || '' === $v) {
            return null;
        }
        if (\is_int($v)) {
            return $v > 0 ? $v : null;
        }
        if (\is_string($v) && is_numeric($v)) {
            $i = (int) $v;

            return $i > 0 ? $i : null;
        }

        return null;
    }
}
