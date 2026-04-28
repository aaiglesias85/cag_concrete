<?php

namespace App\Dto\Admin\Usuario;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/** usuario_id in admin JSON actions */
final class UsuarioIdRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank(message: 'User id is required.')]
    #[Assert\Positive(message: 'User id must be a positive number.')]
    public ?int $usuario_id = null;

    public static function fromHttpRequest(Request $request): static
    {
        $dto = new self();
        $dto->usuario_id = self::positiveIntOrNull($request->get('usuario_id'));

        return $dto;
    }

    /**
     * @internal
     */
    private static function positiveIntOrNull(mixed $v): ?int
    {
        if (null === $v || false === $v || '' === $v) {
            return null;
        }
        if (\is_int($v)) {
            return $v > 0 ? $v : null;
        }
        if (\is_string($v) && is_numeric($v)) {
            $i = (int) $v;

            return $i > 0 ? $i : null;
        }

        return null;
    }
}
