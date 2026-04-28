<?php

namespace App\Dto\Admin\Item;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ItemSalvarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $unit_id = null;

    #[Assert\NotBlank]
    public ?string $name = null;

    public ?string $description = null;

    #[Assert\NotBlank]
    public ?string $status = null;

    public mixed $bond = null;

    public mixed $yield_calculation = null;

    public ?string $equation_id = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $uid = $request->get('unit_id');
        $d->unit_id = \is_string($uid) || is_numeric($uid) ? (string) $uid : null;
        $d->name = \is_string($x = $request->get('name')) ? $x : null;
        $d->description = \is_string($x = $request->get('description')) ? $x : null;
        $st = $request->get('status');
        $d->status = \is_string($st) ? $st : (is_numeric($st) ? (string) $st : null);
        $d->bond = $request->get('bond');
        $d->yield_calculation = $request->get('yield_calculation');
        $eid = $request->get('equation_id');
        $d->equation_id = \is_string($eid) || is_numeric($eid) ? (string) $eid : null;

        return $d;
    }
}
