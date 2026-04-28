<?php

namespace App\Dto\Admin\Holiday;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class HolidayIdRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank(message: 'Holiday id is required.')]
    #[Assert\Positive]
    public ?int $holiday_id = null;

    public static function fromHttpRequest(Request $request): static
    {
        $dto = new self();
        $dto->holiday_id = self::positiveIntOrNull($request->get('holiday_id'));

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
