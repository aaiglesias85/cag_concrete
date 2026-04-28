<?php

namespace App\Dto\Admin\ReporteSubcontractor;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use App\Http\DataTablesHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * DataTables reporte subcontractors + filtros.
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
final class ReporteSubcontractorListarRequest implements AdminHttpRequestDtoInterface
{
    /** @var ParsedDt */
    public array $dt;

    public ?string $subcontractor_id = null;

    public ?string $project_id = null;

    public ?string $project_item_id = null;

    public ?string $fechaInicial = null;

    public ?string $fechaFin = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['id', 'date', 'project', 'subcontractor', 'item', 'unit', 'quantity', 'price', 'total'],
            defaultOrderField: 'date'
        );
        $d->subcontractor_id = self::strOrNull($request->get('subcontractor_id'));
        $d->project_id = self::strOrNull($request->get('project_id'));
        $d->project_item_id = self::strOrNull($request->get('project_item_id'));
        $d->fechaInicial = self::strOrNull($request->get('fechaInicial'));
        $d->fechaFin = self::strOrNull($request->get('fechaFin'));

        return $d;
    }

    private static function strOrNull(mixed $v): ?string
    {
        if (null === $v || false === $v) {
            return null;
        }
        if (\is_string($v) || is_numeric($v)) {
            return (string) $v;
        }

        return null;
    }
}
