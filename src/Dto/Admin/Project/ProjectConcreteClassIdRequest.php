<?php

namespace App\Dto\Admin\Project;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ProjectConcreteClassIdRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank(message: 'concrete_class_id is required.')]
    #[Assert\Positive]
    public ?int $concrete_class_id = null;

    public static function fromHttpRequest(Request $request): static
    {
        $dto = new self();
        $v = $request->get('concrete_class_id');
        if (null === $v || false === $v || '' === $v) {
            $dto->concrete_class_id = null;
        } elseif (\is_int($v)) {
            $dto->concrete_class_id = $v > 0 ? $v : null;
        } elseif (\is_string($v) && is_numeric($v)) {
            $i = (int) $v;
            $dto->concrete_class_id = $i > 0 ? $i : null;
        } else {
            $dto->concrete_class_id = null;
        }

        return $dto;
    }
}
