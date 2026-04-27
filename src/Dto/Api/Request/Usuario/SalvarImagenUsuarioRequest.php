<?php

namespace App\Dto\Api\Request\Usuario;

use App\Dto\Api\Request\Common\JsonRequestBody;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Body JSON de POST /api/usuario/salvarImagen.
 */
final class SalvarImagenUsuarioRequest
{
    #[Assert\NotBlank(message: 'api.validation.imagen_required')]
    public ?string $imagen = null;

    /**
     * @throws \Exception
     */
    public static function fromHttpRequest(Request $request): self
    {
        $data = JsonRequestBody::decodeAssociative($request);
        $dto = new self();
        $dto->imagen = isset($data['imagen']) && \is_string($data['imagen']) ? $data['imagen'] : null;

        return $dto;
    }
}
