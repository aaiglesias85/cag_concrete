<?php

namespace App\Dto\Admin\Schedule;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ScheduleIdRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank(message: 'Schedule id is required.')]
    #[Assert\Positive]
    public ?int $schedule_id = null;

    public static function fromHttpRequest(Request $request): static
    {
        $dto = new self();
        $dto->schedule_id = self::positiveIntOrNull($request->get('schedule_id'));

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
