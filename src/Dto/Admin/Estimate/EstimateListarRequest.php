<?php

namespace App\Dto\Admin\Estimate;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use App\Http\DataTablesHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * DataTables listar estimates + filtros.
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
final class EstimateListarRequest implements AdminHttpRequestDtoInterface
{
    /** @var ParsedDt */
    public array $dt;

    public ?string $stage_id = null;

    public ?string $project_type_id = null;

    public ?string $proposal_type_id = null;

    public ?string $status_id = null;

    public ?string $county_id = null;

    public ?string $district_id = null;

    public ?string $fechaInicial = null;

    public ?string $fechaFin = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['id', 'name', 'company', 'bidDeadline', 'estimators', 'stage'],
            defaultOrderField: 'name'
        );
        foreach (['stage_id', 'project_type_id', 'proposal_type_id', 'status_id', 'county_id', 'district_id', 'fechaInicial', 'fechaFin'] as $k) {
            $d->{$k} = self::s($request->get($k));
        }

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
