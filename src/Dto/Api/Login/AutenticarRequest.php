<?php

namespace App\Dto\Api\Login;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Cuerpo JSON de POST /api/{lang}/login/autenticar.
 */
final class AutenticarRequest
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\NotBlank]
    public ?string $password = null;

    public ?string $player_id = null;

    public ?string $push_token = null;

    #[Assert\Choice(choices: ['ios', 'android', 'web'])]
    public ?string $plataforma = null;
}
