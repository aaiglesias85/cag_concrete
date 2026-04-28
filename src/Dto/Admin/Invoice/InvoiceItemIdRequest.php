<?php

namespace App\Dto\Admin\Invoice;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class InvoiceItemIdRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank(message: 'Invoice item id is required.')]
    #[Assert\Positive]
    public ?int $invoice_item_id = null;

    public static function fromHttpRequest(Request $request): static
    {
        $dto = new self();
        $dto->invoice_item_id = self::positiveIntOrNull($request->get('invoice_item_id'));

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
