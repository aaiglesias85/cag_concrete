<?php

namespace App\Dto\Api\Request\Login;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use App\Dto\Api\Request\Common\JsonRequestBody;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Cuerpo JSON de POST /api/{lang}/login/olvido-Contrasenna; también formulario admin (POST email).
 */
final class OlvidoContrasennaRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    /**
     * API JSON: cuerpo `{"email": "..."}`. Formulario admin: campo `email` (no JSON).
     */
    public static function fromHttpRequest(Request $request): static
    {
        $contentType = $request->headers->get('Content-Type', '');
        if (str_contains($contentType, 'application/json')) {
            $data = JsonRequestBody::decodeAssociative($request);
            $dto = new self();
            $v = $data['email'] ?? null;
            $dto->email = \is_string($v) ? trim($v) : null;

            return $dto;
        }

        $dto = new self();
        $v = $request->get('email');
        $dto->email = \is_string($v) ? trim($v) : null;

        return $dto;
    }
}
