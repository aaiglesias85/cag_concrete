<?php

namespace App\Dto\Admin\Material;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class MaterialSalvarRequest
{
    public ?string $material_id = null;

    #[Assert\NotBlank]
    public ?string $name = null;

    #[Assert\NotBlank]
    public ?string $price = null;

    #[Assert\NotBlank]
    public ?string $unit_id = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $mid = $request->get('material_id');
        $d->material_id = \is_string($mid) || is_numeric($mid) ? (string) $mid : null;
        $d->name = \is_string($x = $request->get('name')) ? $x : null;
        $p = $request->get('price');
        $d->price = (null !== $p && false !== $p && '' !== $p) ? (string) $p : null;
        $uid = $request->get('unit_id');
        $d->unit_id = \is_string($uid) || is_numeric($uid) ? (string) $uid : null;

        return $d;
    }
}
