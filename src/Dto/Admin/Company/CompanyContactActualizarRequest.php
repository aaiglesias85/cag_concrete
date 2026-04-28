<?php

namespace App\Dto\Admin\Company;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class CompanyContactActualizarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank(message: 'Contact id is required.')]
    #[Assert\Positive]
    public ?int $contact_id = null;

    #[Assert\NotBlank(message: 'Company id is required.')]
    #[Assert\Positive]
    public ?int $company_id = null;

    #[Assert\NotBlank]
    public ?string $name = null;

    public ?string $phone = null;

    public ?string $email = null;

    public ?string $role = null;

    public ?string $notes = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->contact_id = self::positiveIntOrNull($request->get('contact_id'));
        $d->company_id = self::positiveIntOrNull($request->get('company_id'));
        $d->name = \is_string($x = $request->get('name')) ? $x : null;
        $d->phone = \is_string($x = $request->get('phone')) ? $x : null;
        $d->email = \is_string($x = $request->get('email')) ? $x : null;
        $d->role = \is_string($x = $request->get('role')) ? $x : null;
        $d->notes = \is_string($x = $request->get('notes')) ? $x : null;

        return $d;
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
