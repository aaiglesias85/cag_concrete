<?php

namespace App\Dto\Admin\Project;

use Symfony\Component\HttpFoundation\Request;

final class ProjectListarNotesFiltroRequest
{
    public ?string $project_id = null;

    public ?string $fechaInicial = null;

    public ?string $fechaFin = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        foreach (['project_id', 'fechaInicial', 'fechaFin'] as $k) {
            $v = $request->get($k);
            $d->{$k} = \is_string($v) || is_numeric($v) ? (string) $v : null;
        }

        return $d;
    }
}
