<?php

namespace App\Dto\Admin\Equation;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class EquationSalvarPayItemsRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank(message: 'pay_items is required.')]
    public ?string $pay_items = null;

    public static function fromHttpRequest(Request $request): static
    {
        $dto = new self();
        $dto->pay_items = \is_string($x = $request->get('pay_items')) ? $x : null;

        return $dto;
    }
}
