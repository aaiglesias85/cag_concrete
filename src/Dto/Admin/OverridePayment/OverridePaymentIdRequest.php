<?php

namespace App\Dto\Admin\OverridePayment;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class OverridePaymentIdRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank(message: 'id is required.')]
    #[Assert\Positive]
    public ?int $id = null;

    public static function fromHttpRequest(Request $request): static
    {
        $dto = new self();
        $dto->id = self::positiveIntOrNull($request->get('id'));

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
