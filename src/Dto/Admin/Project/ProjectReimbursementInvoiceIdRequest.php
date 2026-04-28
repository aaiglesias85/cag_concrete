<?php

namespace App\Dto\Admin\Project;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ProjectReimbursementInvoiceIdRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $invoice_id = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $raw = $request->request->get('invoice_id');
        if (null === $raw) {
            $raw = $request->get('invoice_id');
        }
        if (null === $raw || false === $raw || '' === $raw) {
            $d->invoice_id = null;
        } else {
            $d->invoice_id = (string) $raw;
        }

        return $d;
    }
}
