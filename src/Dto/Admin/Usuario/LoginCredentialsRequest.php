<?php

namespace App\Dto\Admin\Usuario;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/** POST /usuario/autenticar (admin web login) */
final class LoginCredentialsRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\NotBlank]
    public ?string $password = null;

    public static function fromHttpRequest(Request $request): static
    {
        $dto = new self();
        $e = $request->get('email');
        $dto->email = \is_string($e) ? trim($e) : null;
        $p = $request->get('password');
        $dto->password = \is_string($p) ? $p : null;

        return $dto;
    }
}
