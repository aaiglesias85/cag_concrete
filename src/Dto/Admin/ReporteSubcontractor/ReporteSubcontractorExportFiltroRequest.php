<?php

namespace App\Dto\Admin\ReporteSubcontractor;

use Symfony\Component\HttpFoundation\Request;

/**
 * exportarExcel + devolverTotal.
 */
final class ReporteSubcontractorExportFiltroRequest
{
    public ?string $search = null;

    public ?string $subcontractor_id = null;

    public ?string $project_id = null;

    public ?string $project_item_id = null;

    public ?string $fecha_inicial = null;

    public ?string $fecha_fin = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $d->search = self::strOrNull($request->get('search'));
        $d->subcontractor_id = self::strOrNull($request->get('subcontractor_id'));
        $d->project_id = self::strOrNull($request->get('project_id'));
        $d->project_item_id = self::strOrNull($request->get('project_item_id'));
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
