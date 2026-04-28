<?php

namespace App\Dto\Admin\DataTracking;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class DataTrackingArchivoRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank(message: 'archivo is required.')]
    public ?string $archivo = null;

    public static function fromHttpRequest(Request $request): static
    {
        $dto = new self();
        $dto->archivo = \is_string($x = $request->get('archivo')) ? $x : null;

        return $dto;
    }
}
