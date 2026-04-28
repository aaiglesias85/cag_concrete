<?php

namespace App\Dto\Admin\OverheadPrice;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class OverheadPriceIdRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank(message: 'Overhead id is required.')]
    #[Assert\Positive]
    public ?int $overhead_id = null;

    public static function fromHttpRequest(Request $request): static
    {
        $dto = new self();
        $dto->overhead_id = self::positiveIntOrNull($request->get('overhead_id'));

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
