<?php

namespace App\Dto\Admin\Advertisement;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use App\Http\DataTablesHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * Parámetros del listado DataTables de advertisements + filtros de fecha opcionales.
 *
 * @phpstan-type ParsedDt array{
 *   draw:int,
 *   start:int,
 *   length:int,
 *   search:string,
 *   orderField:string,
 *   orderDir:'asc'|'desc',
 *   columns:array,
 *   raw:array
 * }
 */
final class AdvertisementListarRequest implements AdminHttpRequestDtoInterface
{
    /** @var ParsedDt */
    public array $dt;

    public mixed $fecha_inicial = null;

    public mixed $fecha_fin = null;

    public static function fromHttpRequest(Request $request): static
    {
        $self = new self();
        $self->dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['id', 'title', 'startDate', 'endDate', 'status'],
            defaultOrderField: 'startDate'
        );
        $self->fecha_inicial = $request->get('fechaInicial');
        $self->fecha_fin = $request->get('fechaFin');

        return $self;
    }
}
