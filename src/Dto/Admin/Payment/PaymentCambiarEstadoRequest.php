<?php

namespace App\Dto\Admin\Payment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class PaymentCambiarEstadoRequest
{
    #[Assert\NotBlank(message: 'Invoice id is required.')]
    #[Assert\Positive]
    public ?int $invoice_id = null;

    /** 0 = Open, 1 = Closed */
    #[Assert\NotBlank]
    public ?string $status = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $d->invoice_id = self::positiveIntOrNull($request->get('invoice_id'));
        $st = $request->get('status');
        if (null === $st || false === $st) {
            $d->status = null;
        } else {
            $d->status = (string) $st;
        }

        return $d;
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
