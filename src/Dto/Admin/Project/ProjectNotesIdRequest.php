<?php

namespace App\Dto\Admin\Project;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ProjectNotesIdRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank(message: 'notes_id is required.')]
    #[Assert\Positive]
    public ?int $notes_id = null;

    public static function fromHttpRequest(Request $request): static
    {
        $dto = new self();
        $v = $request->get('notes_id');
        if (null === $v || false === $v || '' === $v) {
            $dto->notes_id = null;
        } elseif (\is_int($v)) {
            $dto->notes_id = $v > 0 ? $v : null;
        } elseif (\is_string($v) && is_numeric($v)) {
            $i = (int) $v;
            $dto->notes_id = $i > 0 ? $i : null;
        } else {
            $dto->notes_id = null;
        }

        return $dto;
    }
}
