<?php

namespace App\Dto\Admin\OverridePayment;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class OverridePaymentActualizarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $project_id = null;

    #[Assert\NotBlank]
    public ?string $fechaFin = null;

    /** Puede ser JSON string, array o null. */
    public string|array|null $items = null;

    #[Assert\Positive]
    public ?int $invoice_override_payment_id = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->project_id = (string) $request->get('project_id', '');
        if ('' === trim((string) $d->project_id)) {
            $d->project_id = null;
        }
        $d->fechaFin = (string) $request->get('fechaFin', '');
        if ('' === trim((string) $d->fechaFin)) {
            $d->fechaFin = null;
        }
        $itemsRaw = $request->get('items');
        if (\is_string($itemsRaw) || is_array($itemsRaw)) {
            $d->items = $itemsRaw;
        } else {
            $d->items = null;
        }
        $iop = $request->get('invoice_override_payment_id');
        if (null !== $iop && '' !== (string) $iop) {
            $hid = (int) $iop;
            if ($hid > 0) {
                $d->invoice_override_payment_id = $hid;
            }
        }

        return $d;
    }
}
