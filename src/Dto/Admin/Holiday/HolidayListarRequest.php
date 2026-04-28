<?php

namespace App\Dto\Admin\Holiday;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use App\Http\DataTablesHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * @phpstan-type ParsedDt array{draw:int,start:int,length:int,search:string,orderField:string,orderDir:'asc'|'desc',columns:array,raw:array}
 */
final class HolidayListarRequest implements AdminHttpRequestDtoInterface
{
    /** @var ParsedDt */
    public array $dt;

    public mixed $fecha_inicial = null;

    public mixed $fecha_fin = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['id', 'description', 'day'],
            defaultOrderField: 'day'
        );
        $d->fecha_inicial = $request->get('fechaInicial');
        $d->fecha_fin = $request->get('fechaFin');

        return $d;
    }
}
