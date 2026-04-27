<?php

namespace App\Dto\Admin\Estimate;

use Symfony\Component\HttpFoundation\Request;

/**
 * Filtros DataTable listar (fechaInicial / fechaFin).
 */
final class EstimateListarFiltroRequest
{
    public ?string $stage_id = null;

    public ?string $project_type_id = null;

    public ?string $proposal_type_id = null;

    public ?string $status_id = null;

    public ?string $county_id = null;

    public ?string $district_id = null;

    public ?string $fechaInicial = null;

    public ?string $fechaFin = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
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
