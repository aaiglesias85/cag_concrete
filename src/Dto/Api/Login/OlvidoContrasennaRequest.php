<?php

namespace App\Dto\Api\Login;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Cuerpo JSON de POST /api/{lang}/login/olvido-Contrasenna.
 */
final class OlvidoContrasennaRequest
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;
}
