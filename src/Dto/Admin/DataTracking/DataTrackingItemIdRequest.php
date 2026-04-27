<?php

namespace App\Dto\Admin\DataTracking;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class DataTrackingItemIdRequest
{
    #[Assert\NotBlank(message: 'Data tracking item id is required.')]
    #[Assert\Positive]
    public ?int $data_tracking_item_id = null;

    public static function fromHttpRequest(Request $request): self
    {
        $dto = new self();
        $dto->data_tracking_item_id = self::positiveIntOrNull($request->get('data_tracking_item_id'));

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
