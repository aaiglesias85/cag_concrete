<?php

namespace App\Dto\Admin\Project;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ProjectAgregarItemRequest
{
    public ?string $project_item_id = null;

    #[Assert\NotBlank]
    public ?string $project_id = null;

    public ?string $item_id = null;

    public ?string $item = null;

    public ?string $unit_id = null;

    public ?string $quantity = null;

    public ?string $price = null;

    public ?string $yield_calculation = null;

    public ?string $equation_id = null;

    public mixed $change_order = null;

    public ?string $change_order_date = null;

    public mixed $apply_retainage = null;

    public mixed $bond = null;

    public mixed $bonded = null;

    public ?string $code = null;

    public ?string $contract_name = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $d->project_item_id = self::s($request->get('project_item_id'));
        $d->project_id = self::s($request->get('project_id'));
        $d->item_id = self::s($request->get('item_id'));
        $d->item = self::s($request->get('item'));
        $d->unit_id = self::s($request->get('unit_id'));
        $d->quantity = self::s($request->get('quantity'));
        $d->price = self::s($request->get('price'));
        $d->yield_calculation = self::s($request->get('yield_calculation'));
        $d->equation_id = self::s($request->get('equation_id'));
        $d->change_order = $request->get('change_order');
        $d->change_order_date = self::s($request->get('change_order_date'));
        $d->apply_retainage = $request->get('apply_retainage');
        $d->bond = $request->get('bond');
        $d->bonded = $request->get('bonded');
        $d->code = self::s($request->get('code'));
        $d->contract_name = self::s($request->get('contract_name'));

        return $d;
    }

    private static function s(mixed $v): ?string
    {
        if (null === $v || false === $v) {
            return null;
        }
        if (\is_string($v) || is_numeric($v)) {
            return (string) $v;
        }

        return null;
    }
}
