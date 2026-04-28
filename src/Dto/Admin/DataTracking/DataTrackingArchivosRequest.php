<?php

namespace App\Dto\Admin\DataTracking;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/** Lista separada por comas (mismo criterio que el servicio). */
final class DataTrackingArchivosRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank(message: 'archivos is required.')]
    public ?string $archivos = null;

    public static function fromHttpRequest(Request $request): static
    {
        $dto = new self();
        $dto->archivos = \is_string($x = $request->get('archivos')) ? $x : null;

        return $dto;
    }
}
