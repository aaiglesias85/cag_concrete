<?php

namespace App\Dto\Admin\ReporteEmployee;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use App\Http\DataTablesHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * DataTables reporte empleados + filtros.
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
final class ReporteEmployeeListarRequest implements AdminHttpRequestDtoInterface
{
    /** @var ParsedDt */
    public array $dt;

    public ?string $employee_id = null;

    public ?string $project_id = null;

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
        $d->employee_id = self::strOrNull($request->get('employee_id'));
        $d->project_id = self::strOrNull($request->get('project_id'));
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
