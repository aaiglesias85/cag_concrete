<?php

namespace App\Dto\Api\Login;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Cuerpo JSON de POST /api/{lang}/login/olvido-Contrasenna; también formulario admin (POST email).
 */
final class OlvidoContrasennaRequest
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    public static function fromHttpRequest(Request $request): self
    {
        $dto = new self();
        $v = $request->get('email');
        $dto->email = \is_string($v) ? trim($v) : null;

        return $dto;
    }
}
