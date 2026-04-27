<?php

namespace App\Dto\Admin\Invoice;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class InvoiceSalvarRequest
{
    public ?string $invoice_id = null;

    #[Assert\NotBlank]
    public ?string $number = null;

    #[Assert\NotBlank]
    public ?string $project_id = null;

    #[Assert\NotBlank]
    public ?string $start_date = null;

    #[Assert\NotBlank]
    public ?string $end_date = null;

    public ?string $notes = null;

    public ?string $paid = null;

    public ?string $items = null;

    public ?string $exportar = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $iid = $request->get('invoice_id');
        $d->invoice_id = \is_string($iid) || is_numeric($iid) ? (string) $iid : null;
        $num = $request->get('number');
        $d->number = \is_string($num) || is_numeric($num) ? (string) $num : null;
        $pid = $request->get('project_id');
        $d->project_id = \is_string($pid) || is_numeric($pid) ? (string) $pid : null;
        $d->start_date = \is_string($x = $request->get('start_date')) ? $x : null;
        $d->end_date = \is_string($x = $request->get('end_date')) ? $x : null;
        $d->notes = \is_string($x = $request->get('notes')) ? $x : null;
        $p = $request->get('paid');
        $d->paid = \is_string($p) || is_numeric($p) ? (string) $p : null;
        $d->items = \is_string($x = $request->get('items')) ? $x : null;
        $ex = $request->get('exportar');
        $d->exportar = \is_string($ex) || is_numeric($ex) ? (string) $ex : null;

        return $d;
    }
}
