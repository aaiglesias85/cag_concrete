<?php

namespace App\Dto\Admin\ConcreteVendor;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ConcreteVendorActualizarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $vendor_id = null;

    #[Assert\NotBlank]
    public ?string $name = null;

    public ?string $phone = null;

    public ?string $address = null;

    public ?string $contactName = null;

    public ?string $contactEmail = null;

    /** JSON string; se decodifica en el controlador. */
    public ?string $contacts = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $vid = $request->get('vendor_id');
        $d->vendor_id = \is_string($vid) || is_numeric($vid) ? (string) $vid : null;
        $d->name = \is_string($x = $request->get('name')) ? $x : null;
        $d->phone = \is_string($x = $request->get('phone')) ? $x : null;
        $d->address = \is_string($x = $request->get('address')) ? $x : null;
        $d->contactName = \is_string($x = $request->get('contactName')) ? $x : null;
        $d->contactEmail = \is_string($x = $request->get('contactEmail')) ? $x : null;
        $d->contacts = \is_string($x = $request->get('contacts')) ? $x : null;

        return $d;
    }
}
