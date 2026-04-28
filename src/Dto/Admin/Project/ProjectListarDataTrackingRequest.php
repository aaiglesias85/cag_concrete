<?php

namespace App\Dto\Admin\Project;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use App\Http\DataTablesHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * DataTables datatracking dentro de la ficha proyecto.
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
final class ProjectListarDataTrackingRequest implements AdminHttpRequestDtoInterface
{
    /** @var ParsedDt */
    public array $dt;

    public ?string $project_id = null;

    public ?string $pending = null;

    public ?string $fechaInicial = null;

    public ?string $fechaFin = null;

    public ?string $only_punch = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['id', 'date', 'leads', 'totalConcUsed', 'total_concrete_yiel', 'lostConcrete', 'total_concrete', 'totalLabor', 'total_daily_today', 'profit'],
            defaultOrderField: 'date'
        );
        $d->project_id = self::s($request->get('project_id'));
        $d->pending = self::s($request->get('pending'));
        $d->fechaInicial = self::s($request->get('fechaInicial'));
        $d->fechaFin = self::s($request->get('fechaFin'));
        $d->only_punch = self::s($request->get('only_punch'));

        return $d;
    }

    private static function s(mixed $v): ?string
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
