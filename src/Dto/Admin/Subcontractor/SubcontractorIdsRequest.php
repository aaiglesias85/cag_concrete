<?php

namespace App\Dto\Admin\Subcontractor;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class SubcontractorIdsRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank(message: 'At least one id is required.')]
    public ?string $ids = null;

    public static function fromHttpRequest(Request $request): static
    {
        $dto = new self();
        $dto->ids = \is_string($x = $request->get('ids')) ? $x : null;

        return $dto;
    }
}
