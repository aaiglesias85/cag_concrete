<?php

namespace App\Dto\Admin\Invoice;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * id vacío = alta; en otro caso edición.
 */
final class InvoiceValidarRequest implements AdminHttpRequestDtoInterface
{
    public ?string $invoice_id = null;

    #[Assert\NotBlank]
    public ?string $project_id = null;

    #[Assert\NotBlank]
    public ?string $start_date = null;

    #[Assert\NotBlank]
    public ?string $end_date = null;

    #[Assert\NotBlank]
    public ?string $number = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $iid = $request->get('invoice_id');
        $d->invoice_id = \is_string($iid) || is_numeric($iid) ? (string) $iid : null;
        $pid = $request->get('project_id');
        $d->project_id = \is_string($pid) || is_numeric($pid) ? (string) $pid : null;
        $d->start_date = \is_string($x = $request->get('start_date')) ? $x : null;
        $d->end_date = \is_string($x = $request->get('end_date')) ? $x : null;
        $num = $request->get('number');
        $d->number = \is_string($num) || is_numeric($num) ? (string) $num : null;

        return $d;
    }
}
