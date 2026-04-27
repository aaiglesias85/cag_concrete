<?php

namespace App\Dto\Admin\Default;

use Symfony\Component\HttpFoundation\Request;

/**
 * Filtros del dashboard (AJAX). Todos opcionales.
 */
final class DashboardListarStatsRequest
{
    public ?string $project_id = null;

    public ?string $status = null;

    public ?string $fechaInicial = null;

    public ?string $fechaFin = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $d->project_id = self::strOrNull($request->get('project_id'));
        $d->status = self::strOrNull($request->get('status'));
        $d->fechaInicial = self::strOrNull($request->get('fechaInicial'));
        $d->fechaFin = self::strOrNull($request->get('fechaFin'));

        return $d;
    }

    private static function strOrNull(mixed $v): ?string
    {
        if (null === $v || false === $v) {
            return null;
        }
        if ('' === $v) {
            return '';
        }
        if (\is_string($v) || is_numeric($v)) {
            return (string) $v;
        }

        return null;
    }
}
