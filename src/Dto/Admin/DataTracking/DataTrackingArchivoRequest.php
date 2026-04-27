<?php

namespace App\Dto\Admin\DataTracking;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class DataTrackingArchivoRequest
{
    #[Assert\NotBlank(message: 'archivo is required.')]
    public ?string $archivo = null;

    public static function fromHttpRequest(Request $request): self
    {
        $dto = new self();
        $dto->archivo = \is_string($x = $request->get('archivo')) ? $x : null;

        return $dto;
    }
}
