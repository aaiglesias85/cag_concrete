<?php

namespace App\Dto\Admin\Project;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/** eliminarAjustePrecio: id de ajuste */
final class ProjectAjusteRowIdRequest
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $id = null;

    public static function fromHttpRequest(Request $request): self
    {
        $dto = new self();
        $v = $request->get('id');
        if (null === $v || false === $v || '' === $v) {
            $dto->id = null;
        } elseif (\is_int($v)) {
            $dto->id = $v > 0 ? $v : null;
        } elseif (\is_string($v) && is_numeric($v)) {
            $i = (int) $v;
            $dto->id = $i > 0 ? $i : null;
        } else {
            $dto->id = null;
        }

        return $dto;
    }
}
