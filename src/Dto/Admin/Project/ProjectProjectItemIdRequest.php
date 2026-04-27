<?php

namespace App\Dto\Admin\Project;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ProjectProjectItemIdRequest
{
    #[Assert\NotBlank(message: 'project_item_id is required.')]
    #[Assert\Positive]
    public ?int $project_item_id = null;

    public static function fromHttpRequest(Request $request): self
    {
        $dto = new self();
        $v = $request->get('project_item_id');
        if (null === $v || false === $v || '' === $v) {
            $dto->project_item_id = null;
        } elseif (\is_int($v)) {
            $dto->project_item_id = $v > 0 ? $v : null;
        } elseif (\is_string($v) && is_numeric($v)) {
            $i = (int) $v;
            $dto->project_item_id = $i > 0 ? $i : null;
        } else {
            $dto->project_item_id = null;
        }

        return $dto;
    }
}
