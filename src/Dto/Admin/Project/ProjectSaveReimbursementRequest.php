<?php

namespace App\Dto\Admin\Project;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ProjectSaveReimbursementRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $invoice_id = null;

    #[Assert\NotBlank]
    public ?string $amount = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->invoice_id = self::s($request->request->get('invoice_id') ?? $request->get('invoice_id'));
        $d->amount = self::s($request->request->get('amount') ?? $request->get('amount'));

        return $d;
    }

    private static function s(mixed $v): ?string
    {
        if (null === $v || false === $v || '' === $v) {
            return null;
        }
        if (\is_string($v) || is_numeric($v)) {
            return (string) $v;
        }

        return null;
    }
}
