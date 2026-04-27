<?php

namespace App\Dto\Admin\ProjectType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ProjectTypeSalvarRequest
{
    public ?string $type_id = null;

    #[Assert\NotBlank]
    public ?string $description = null;

    #[Assert\NotBlank]
    public ?string $status = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $id = $request->get('type_id');
        $d->type_id = \is_string($id) || is_numeric($id) ? (string) $id : null;
        $d->description = \is_string($x = $request->get('description')) ? $x : null;
        $st = $request->get('status');
        $d->status = \is_string($st) ? $st : (is_numeric($st) ? (string) $st : null);

        return $d;
    }
}
