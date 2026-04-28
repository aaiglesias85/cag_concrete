<?php

namespace App\Dto\Admin\Project;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use App\Http\DataTablesHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * DataTables listar proyectos + filtros de la vista.
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
final class ProjectListarRequest implements AdminHttpRequestDtoInterface
{
    /** @var ParsedDt */
    public array $dt;

    public ?string $company_id = null;

    public ?string $status = null;

    public ?string $fechaInicial = null;

    public ?string $fechaFin = null;

    public ?string $missing_info = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['id', 'projectNumber', 'subcontract', 'status', 'name', 'dueDate', 'company', 'nota'],
            defaultOrderField: 'projectNumber'
        );
        foreach (['company_id', 'status', 'fechaInicial', 'fechaFin', 'missing_info'] as $k) {
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
