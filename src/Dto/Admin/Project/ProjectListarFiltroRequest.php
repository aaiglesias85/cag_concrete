<?php

namespace App\Dto\Admin\Project;

use Symfony\Component\HttpFoundation\Request;

final class ProjectListarFiltroRequest
{
    public ?string $company_id = null;

    public ?string $status = null;

    public ?string $fechaInicial = null;

    public ?string $fechaFin = null;

    public ?string $missing_info = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
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
