<?php

namespace App\Dto\Admin\OverheadPrice;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class OverheadPriceSalvarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $name = null;

    #[Assert\NotBlank]
    public ?string $price = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->name = \is_string($x = $request->get('name')) ? $x : null;
        $p = $request->get('price');
        $d->price = \is_string($p) || is_numeric($p) ? (string) $p : null;

        return $d;
    }
}
