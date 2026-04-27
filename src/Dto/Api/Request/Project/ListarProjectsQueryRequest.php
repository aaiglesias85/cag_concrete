<?php

namespace App\Dto\Api\Request\Project;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Query string de GET /api/{lang}/project/listar.
 */
final class ListarProjectsQueryRequest
{
    public string $search = '';

    #[Assert\Length(max: 64)]
    public string $empresa_id = '';

    #[Assert\Length(max: 32)]
    public string $fecha_inicial = '';

    #[Assert\Length(max: 32)]
    public string $fecha_fin = '';

    #[Assert\Positive]
    public int $limit = 100;

    #[Assert\PositiveOrZero]
    public int $offset = 0;

    public static function fromHttpRequest(Request $request): self
    {
        $self = new self();
        $self->search = (string) $request->query->get('search', '');
        $self->empresa_id = (string) $request->query->get('empresa_id', '');
        $self->fecha_inicial = (string) $request->query->get('fecha_inicial', '');
        $self->fecha_fin = (string) $request->query->get('fecha_fin', '');

        $limit = (int) $request->query->get('limit', 100);
        $self->limit = $limit > 0 ? $limit : 100;

        $offset = (int) $request->query->get('offset', 0);
        $self->offset = $offset >= 0 ? $offset : 0;

        return $self;
    }
}
