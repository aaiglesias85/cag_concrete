<?php

namespace App\Dto\Api\Usuario;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Body JSON de POST /api/usuario/salvarImagen.
 */
final class SalvarImagenUsuarioRequest
{
    #[Assert\NotBlank(message: 'api.validation.imagen_required')]
    public ?string $imagen = null;
}
