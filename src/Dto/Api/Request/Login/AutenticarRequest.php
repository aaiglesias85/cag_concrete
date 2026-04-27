<?php

namespace App\Dto\Api\Request\Login;

use App\Dto\Api\Request\Common\JsonRequestBody;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @throws \Exception
     */
    public static function fromHttpRequest(Request $request): self
    {
        $data = JsonRequestBody::decodeAssociative($request);
        $dto = new self();
        $dto->email = isset($data['email']) && \is_string($data['email']) ? trim($data['email']) : null;
        $dto->password = isset($data['password']) && \is_string($data['password']) ? $data['password'] : null;
        $dto->player_id = isset($data['player_id']) && \is_string($data['player_id']) ? $data['player_id'] : null;
        $dto->push_token = isset($data['push_token']) && \is_string($data['push_token']) ? $data['push_token'] : null;
        $dto->plataforma = isset($data['plataforma']) && \is_string($data['plataforma']) ? $data['plataforma'] : null;

        return $dto;
    }
}
