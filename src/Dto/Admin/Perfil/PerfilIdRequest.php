<?php

namespace App\Dto\Admin\Perfil;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class PerfilIdRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank(message: 'Profile id is required.')]
    #[Assert\Positive]
    public ?int $perfil_id = null;

    public static function fromHttpRequest(Request $request): static
    {
        $dto = new self();
        $dto->perfil_id = self::positiveIntOrNull($request->get('perfil_id'));

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
