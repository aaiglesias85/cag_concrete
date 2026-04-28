<?php

namespace App\Dto\Admin\OverridePayment;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class OverridePaymentIdsRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank(message: 'At least one id is required.')]
    public ?string $ids = null;

    public static function fromHttpRequest(Request $request): static
    {
        $dto = new self();
        $raw = $request->get('ids', '');
        $dto->ids = \is_string($raw) || is_numeric($raw) ? (string) $raw : null;

        return $dto;
    }
}
