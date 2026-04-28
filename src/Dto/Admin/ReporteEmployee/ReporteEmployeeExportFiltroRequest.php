<?php

namespace App\Dto\Admin\ReporteEmployee;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;

/**
 * exportarExcel + devolverTotal: search, ids, rango (fecha_inicial / fecha_fin en minúsculas).
 */
final class ReporteEmployeeExportFiltroRequest implements AdminHttpRequestDtoInterface
{
    public ?string $search = null;

    public ?string $employee_id = null;

    public ?string $project_id = null;

    public ?string $fecha_inicial = null;

    public ?string $fecha_fin = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->search = self::strOrNull($request->get('search'));
        $d->employee_id = self::strOrNull($request->get('employee_id'));
        $d->project_id = self::strOrNull($request->get('project_id'));
        $d->fecha_inicial = self::strOrNull($request->get('fecha_inicial'));
        $d->fecha_fin = self::strOrNull($request->get('fecha_fin'));

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
