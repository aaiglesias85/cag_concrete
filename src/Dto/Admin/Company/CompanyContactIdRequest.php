<?php

namespace App\Dto\Admin\Company;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class CompanyContactIdRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank(message: 'Contact id is required.')]
    #[Assert\Positive]
    public ?int $contact_id = null;

    public static function fromHttpRequest(Request $request): static
    {
        $dto = new self();
        $dto->contact_id = self::positiveIntOrNull($request->get('contact_id'));

        return $dto;
    }

    /**
     * @internal
     */
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
