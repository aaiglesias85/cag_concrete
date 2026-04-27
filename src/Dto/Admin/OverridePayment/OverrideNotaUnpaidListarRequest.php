<?php

namespace App\Dto\Admin\OverridePayment;

use Symfony\Component\HttpFoundation\Request;

final class OverrideNotaUnpaidListarRequest
{
    public string $project_id = '';

    public string $fechaFin = '';

    public int $project_item_id = 0;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $d->project_id = (string) $request->get('project_id', '');
        $d->fechaFin = (string) $request->get('fechaFin', '');
        $d->project_item_id = (int) $request->get('project_item_id', 0);

        return $d;
    }
}
