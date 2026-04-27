<?php

namespace App\Dto\Admin\Project;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ProjectSugerirCodeRequest
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $project_id = null;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $item_id = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $d->project_id = self::pos($request->get('project_id'));
        $d->item_id = self::pos($request->get('item_id'));

        return $d;
    }

    private static function pos(mixed $v): ?int
    {
        if (null === $v || false === $v || '' === $v) {
            return null;
        }
        if (\is_int($v)) {
            return $v > 0 ? $v : null;
        }
        if (\is_string($v) && is_numeric($v)) {
            $i = (int) $v;

            return $i > 0 ? $i : null;
        }

        return null;
    }
}
