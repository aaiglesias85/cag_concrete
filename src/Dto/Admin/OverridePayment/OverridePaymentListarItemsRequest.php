<?php

namespace App\Dto\Admin\OverridePayment;

use Symfony\Component\HttpFoundation\Request;

/** Filtros opcionales (listado de ítems). */
final class OverridePaymentListarItemsRequest
{
    public ?string $company_id = null;

    public ?string $project_id = null;

    public ?string $fechaFin = null;

    public ?int $invoice_override_payment_id = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $c = $request->get('company_id');
        $d->company_id = null !== $c ? (string) $c : null;
        $p = $request->get('project_id');
        $d->project_id = null !== $p ? (string) $p : null;
        $f = $request->get('fechaFin');
        $d->fechaFin = null !== $f ? (string) $f : null;
        $raw = $request->get('invoice_override_payment_id');
        if (null !== $raw && '' !== (string) $raw) {
            $i = (int) $raw;
            if ($i > 0) {
                $d->invoice_override_payment_id = $i;
            }
        }

        return $d;
    }
}
