<?php

namespace App\Dto\Admin\Estimate;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class EstimateAgregarItemRequest
{
    public ?string $estimate_item_id = null;

    #[Assert\NotBlank]
    public ?string $estimate_id = null;

    public ?string $quote_id = null;

    public ?string $item_id = null;

    public ?string $item = null;

    public ?string $unit_id = null;

    public ?string $quantity = null;

    public ?string $price = null;

    public ?string $yield_calculation = null;

    public ?string $equation_id = null;

    public ?string $code = null;

    public ?string $contract_name = null;

    public ?string $new_quote_name = null;

    /** CSV o array según el front; el controlador normaliza */
    public mixed $note_ids = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        foreach (['estimate_item_id', 'estimate_id', 'quote_id', 'item_id', 'item', 'unit_id', 'quantity', 'price', 'yield_calculation', 'equation_id', 'code', 'contract_name', 'new_quote_name'] as $k) {
            $v = $request->get($k);
            $d->{$k} = \is_string($v) || is_numeric($v) ? (string) $v : null;
        }
        $d->note_ids = $request->get('note_ids');

        return $d;
    }
}
