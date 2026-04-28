<?php

namespace App\Dto\Admin\Payment;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class PaymentNotesItemSalvarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $invoice_item_id = null;

    public ?string $notes = null;

    public ?string $override_unpaid_qty = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->invoice_item_id = self::strOrNull($request->get('invoice_item_id'));
        $d->notes = \is_string($x = $request->get('notes')) ? $x : null;
        $d->override_unpaid_qty = self::strOrNull($request->get('override_unpaid_qty'));

        return $d;
    }

    private static function strOrNull(mixed $v): ?string
    {
        if (null === $v || false === $v) {
            return null;
        }
        if ('' === $v) {
            return '';
        }
        if (\is_string($v) || is_numeric($v)) {
            return (string) $v;
        }

        return null;
    }
}
