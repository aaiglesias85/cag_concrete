<?php

namespace App\Dto\Admin\Project;

use Symfony\Component\HttpFoundation\Request;

final class ProjectDataTrackingFiltroRequest
{
    public ?string $project_id = null;

    public ?string $pending = null;

    public ?string $fechaInicial = null;

    public ?string $fechaFin = null;

    public ?string $only_punch = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
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
