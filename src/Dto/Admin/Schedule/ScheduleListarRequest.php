<?php

namespace App\Dto\Admin\Schedule;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use App\Http\DataTablesHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * @phpstan-type ParsedDt array{draw:int,start:int,length:int,search:string,orderField:string,orderDir:'asc'|'desc',columns:array,raw:array}
 */
final class ScheduleListarRequest implements AdminHttpRequestDtoInterface
{
    /** @var ParsedDt */
    public array $dt;

    public mixed $project_id = null;

    public mixed $vendor_id = null;

    public mixed $fecha_inicial = null;

    public mixed $fecha_fin = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['id', 'project', 'concreteVendor', 'description', 'location', 'day', 'hour', 'quantity', 'notes'],
            defaultOrderField: 'day'
        );
        $d->project_id = $request->get('project_id');
        $d->vendor_id = $request->get('vendor_id');
        $d->fecha_inicial = $request->get('fechaInicial');
        $d->fecha_fin = $request->get('fechaFin');

        return $d;
    }
}
