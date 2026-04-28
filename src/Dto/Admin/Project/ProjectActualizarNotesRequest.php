<?php

namespace App\Dto\Admin\Project;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ProjectActualizarNotesRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $notes_id = null;

    #[Assert\NotBlank]
    public ?string $project_id = null;

    public ?string $notes = null;

    public ?string $date = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->notes_id = self::s($request->get('notes_id'));
        $d->project_id = self::s($request->get('project_id'));
        $d->notes = self::s($request->get('notes'));
        $d->date = self::s($request->get('date'));

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
}
