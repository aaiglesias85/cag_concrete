<?php

namespace App\Dto\Admin\Payment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/** Lista separada por comas, según el servicio. */
final class PaymentArchivosRequest
{
    #[Assert\NotBlank(message: 'archivos is required.')]
    public ?string $archivos = null;

    public static function fromHttpRequest(Request $request): self
    {
        $dto = new self();
        $dto->archivos = \is_string($x = $request->get('archivos')) ? $x : null;

        return $dto;
    }
}
