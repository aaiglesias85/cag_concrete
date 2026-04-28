<?php

namespace App\Dto\Admin\DataTracking;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use App\Http\DataTablesHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * @phpstan-type ParsedDt array{draw:int,start:int,length:int,search:string,orderField:string,orderDir:'asc'|'desc',columns:array,raw:array}
 */
final class DataTrackingListarRequest implements AdminHttpRequestDtoInterface
{
    /** @var ParsedDt */
    public array $dt;

    public mixed $project_id = null;

    public mixed $pending = null;

    public mixed $fecha_inicial = null;

    public mixed $fecha_fin = null;

    public mixed $only_punch = '';

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['id', 'date', 'project', 'totalConcUsed', 'total_concrete_yiel', 'lostConcrete', 'total_concrete', 'totalLabor', 'total_daily_today', 'profit'],
            defaultOrderField: 'date'
        );
        $d->project_id = $request->get('project_id');
        $d->pending = $request->get('pending');
        $d->fecha_inicial = $request->get('fechaInicial');
        $d->fecha_fin = $request->get('fechaFin');
        $d->only_punch = $request->get('only_punch', '');

        return $d;
    }
}
