<?php

namespace App\Dto\Admin\EmployeeRole;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class EmployeeRoleIdRequest
{
    #[Assert\NotBlank(message: 'Role id is required.')]
    #[Assert\Positive]
    public ?int $role_id = null;

    public static function fromHttpRequest(Request $request): self
    {
        $dto = new self();
        $dto->role_id = self::positiveIntOrNull($request->get('role_id'));

        return $dto;
    }

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
