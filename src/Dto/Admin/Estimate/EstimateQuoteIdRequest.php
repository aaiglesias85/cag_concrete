<?php

namespace App\Dto\Admin\Estimate;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class EstimateQuoteIdRequest
{
    #[Assert\NotBlank(message: 'quote_id is required.')]
    #[Assert\Positive]
    public ?int $quote_id = null;

    public static function fromHttpRequest(Request $request): self
    {
        $dto = new self();
        $v = $request->get('quote_id');
        if (null === $v || false === $v || '' === $v) {
            $dto->quote_id = null;
        } elseif (\is_int($v)) {
            $dto->quote_id = $v > 0 ? $v : null;
        } elseif (\is_string($v) && is_numeric($v)) {
            $i = (int) $v;
            $dto->quote_id = $i > 0 ? $i : null;
        } else {
            $dto->quote_id = null;
        }

        return $dto;
    }
}
