<?php

namespace App\Dto\Api\Project;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Query string de GET /api/{lang}/project/cargarDatos.
 */
final class CargarProyectoDatosRequest
{
    #[Assert\NotBlank(message: 'api.validation.project_id_required')]
    #[Assert\Length(max: 32)]
    public string $project_id = '';

    public static function fromHttpRequest(Request $request): self
    {
        $self = new self();
        $self->project_id = trim((string) ($request->query->get('project_id') ?? ''));

        return $self;
    }
}
