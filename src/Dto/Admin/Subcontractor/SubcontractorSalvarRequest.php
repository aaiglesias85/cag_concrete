<?php

namespace App\Dto\Admin\Subcontractor;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class SubcontractorSalvarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $name = null;

    public ?string $phone = null;

    public ?string $address = null;

    public ?string $contactName = null;

    public ?string $contactEmail = null;

    public ?string $companyName = null;

    public ?string $companyPhone = null;

    public ?string $companyAddress = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->name = \is_string($x = $request->get('name')) ? $x : null;
        $d->phone = \is_string($x = $request->get('phone')) ? $x : null;
        $d->address = \is_string($x = $request->get('address')) ? $x : null;
        $d->contactName = \is_string($x = $request->get('contactName')) ? $x : null;
        $d->contactEmail = \is_string($x = $request->get('contactEmail')) ? $x : null;
        $d->companyName = \is_string($x = $request->get('companyName')) ? $x : null;
        $d->companyPhone = \is_string($x = $request->get('companyPhone')) ? $x : null;
        $d->companyAddress = \is_string($x = $request->get('companyAddress')) ? $x : null;

        return $d;
    }
}
