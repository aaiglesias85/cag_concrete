<?php

namespace App\Dto\Admin\Estimate;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class EstimateEstimateItemIdRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank(message: 'estimate_item_id is required.')]
    #[Assert\Positive]
    public ?int $estimate_item_id = null;

    public static function fromHttpRequest(Request $request): static
    {
        $dto = new self();
        $v = $request->get('estimate_item_id');
        if (null === $v || false === $v || '' === $v) {
            $dto->estimate_item_id = null;
        } elseif (\is_int($v)) {
            $dto->estimate_item_id = $v > 0 ? $v : null;
        } elseif (\is_string($v) && is_numeric($v)) {
            $i = (int) $v;
            $dto->estimate_item_id = $i > 0 ? $i : null;
        } else {
            $dto->estimate_item_id = null;
        }

        return $dto;
    }
}
