<?php

namespace App\Dto\Admin\Payment;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class PaymentNotesSalvarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $invoice_id = null;

    #[Assert\NotBlank]
    public ?string $notes = null;

    #[Assert\NotBlank]
    public ?string $date = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $iid = $request->get('invoice_id');
        $d->invoice_id = \is_string($iid) || is_numeric($iid) ? (string) $iid : null;
        $d->notes = \is_string($x = $request->get('notes')) ? $x : null;
        $d->date = \is_string($x = $request->get('date')) ? $x : null;

        return $d;
    }
}
