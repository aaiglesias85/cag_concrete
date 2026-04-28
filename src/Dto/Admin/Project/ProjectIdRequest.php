<?php

namespace App\Dto\Admin\Project;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ProjectIdRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank(message: 'project_id is required.')]
    #[Assert\Positive]
    public ?int $project_id = null;

    public static function fromHttpRequest(Request $request): static
    {
        $dto = new self();
        $v = $request->get('project_id');
        if (null === $v || false === $v || '' === $v) {
            $dto->project_id = null;
        } elseif (\is_int($v)) {
            $dto->project_id = $v > 0 ? $v : null;
        } elseif (\is_string($v) && is_numeric($v)) {
            $i = (int) $v;
            $dto->project_id = $i > 0 ? $i : null;
        } else {
            $dto->project_id = null;
        }

        return $dto;
    }
}
