<?php

namespace App\Dto\Admin\OverheadPrice;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class OverheadPriceActualizarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $overhead_id = null;

    #[Assert\NotBlank]
    public ?string $name = null;

    #[Assert\NotBlank]
    public ?string $price = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $id = $request->get('overhead_id');
        $d->overhead_id = \is_string($id) || is_numeric($id) ? (string) $id : null;
        $d->name = \is_string($x = $request->get('name')) ? $x : null;
        $p = $request->get('price');
        $d->price = \is_string($p) || is_numeric($p) ? (string) $p : null;

        return $d;
    }
}
