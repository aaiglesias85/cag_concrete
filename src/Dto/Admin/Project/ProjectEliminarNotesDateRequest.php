<?php

namespace App\Dto\Admin\Project;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ProjectEliminarNotesDateRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $project_id = null;

    public ?string $from = null;

    public ?string $to = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->project_id = self::pos($request->get('project_id'));
        $d->from = self::s($request->get('from'));
        $d->to = self::s($request->get('to'));

        return $d;
    }

    private static function s(mixed $v): ?string
    {
        if (null === $v || false === $v) {
            return null;
        }
        if (\is_string($v) || is_numeric($v)) {
            return (string) $v;
        }

        return null;
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
