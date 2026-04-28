<?php

namespace App\Dto\Admin\Project;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use App\Http\DataTablesHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * DataTables notas de proyecto.
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
final class ProjectListarNotesRequest implements AdminHttpRequestDtoInterface
{
    /** @var ParsedDt */
    public array $dt;

    public ?string $project_id = null;

    public ?string $fechaInicial = null;

    public ?string $fechaFin = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['id', 'date', 'notes'],
            defaultOrderField: 'date'
        );
        foreach (['project_id', 'fechaInicial', 'fechaFin'] as $k) {
            $v = $request->get($k);
            $d->{$k} = \is_string($v) || is_numeric($v) ? (string) $v : null;
        }

        return $d;
    }
}
