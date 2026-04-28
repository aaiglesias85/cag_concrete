<?php

namespace App\Dto\Admin\Payment;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class PaymentNotesDateRangeRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $invoice_id = null;

    #[Assert\NotBlank]
    public ?string $from = null;

    #[Assert\NotBlank]
    public ?string $to = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $iid = $request->get('invoice_id');
        $d->invoice_id = \is_string($iid) || is_numeric($iid) ? (string) $iid : null;
        $d->from = \is_string($x = $request->get('from')) ? $x : null;
        $d->to = \is_string($x = $request->get('to')) ? $x : null;

        return $d;
    }
}
