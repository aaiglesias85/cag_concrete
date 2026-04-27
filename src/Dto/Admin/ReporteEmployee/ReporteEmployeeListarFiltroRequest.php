<?php

namespace App\Dto\Admin\ReporteEmployee;

use Symfony\Component\HttpFoundation\Request;

/**
 * Filtros DataTables: employee_id, project_id, rango (fechaInicial / fechaFin con mayúscula I).
 */
final class ReporteEmployeeListarFiltroRequest
{
    public ?string $employee_id = null;

    public ?string $project_id = null;

    public ?string $fechaInicial = null;

    public ?string $fechaFin = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
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
