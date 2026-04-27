<?php

namespace App\Dto\Admin\Project;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ProjectContactIdRequest
{
    #[Assert\NotBlank(message: 'contact_id is required.')]
    #[Assert\Positive]
    public ?int $contact_id = null;

    public static function fromHttpRequest(Request $request): self
    {
        $dto = new self();
        $v = $request->get('contact_id');
        if (null === $v || false === $v || '' === $v) {
            $dto->contact_id = null;
        } elseif (\is_int($v)) {
            $dto->contact_id = $v > 0 ? $v : null;
        } elseif (\is_string($v) && is_numeric($v)) {
            $i = (int) $v;
            $dto->contact_id = $i > 0 ? $i : null;
        } else {
            $dto->contact_id = null;
        }

        return $dto;
    }
}
