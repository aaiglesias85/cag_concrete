<?php

namespace App\Dto\Admin\Task;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use App\Http\DataTablesHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * @phpstan-type ParsedDt array{draw:int,start:int,length:int,search:string,orderField:string,orderDir:'asc'|'desc',columns:array,raw:array}
 */
final class TaskListarRequest implements AdminHttpRequestDtoInterface
{
    /** @var ParsedDt */
    public array $dt;

    public mixed $fecha_inicial = null;

    public mixed $fecha_fin = null;

    public string $statusFiltro = '';

    public string $usuarioFiltro = '';

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['id', 'description', 'due_date', 'status', 'created_at', 'assigned'],
            defaultOrderField: 'due_date'
        );
        $d->fecha_inicial = $request->get('fechaInicial');
        $d->fecha_fin = $request->get('fechaFin');
        $d->statusFiltro = (string) $request->get('statusFiltro', '');
        $d->usuarioFiltro = (string) $request->get('usuarioFiltro', '');

        return $d;
    }
}
