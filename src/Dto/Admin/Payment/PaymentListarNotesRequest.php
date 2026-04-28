<?php

namespace App\Dto\Admin\Payment;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use App\Http\DataTablesHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * @phpstan-type ParsedDt array{draw:int,start:int,length:int,search:string,orderField:string,orderDir:'asc'|'desc',columns:array,raw:array}
 */
final class PaymentListarNotesRequest implements AdminHttpRequestDtoInterface
{
    /** @var ParsedDt */
    public array $dt;

    public mixed $invoice_id = null;

    public mixed $fecha_inicial = null;

    public mixed $fecha_fin = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['id', 'date', 'notes'],
            defaultOrderField: 'date'
        );
        $d->invoice_id = $request->get('invoice_id');
        $d->fecha_inicial = $request->get('fechaInicial');
        $d->fecha_fin = $request->get('fechaFin');

        return $d;
    }
}
