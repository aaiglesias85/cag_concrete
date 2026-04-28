<?php

namespace App\Dto\Admin\ProjectStage;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ProjectStageActualizarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $stage_id = null;

    #[Assert\NotBlank]
    public ?string $description = null;

    public ?string $color = null;

    #[Assert\NotBlank]
    public ?string $status = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $sid = $request->get('stage_id');
        $d->stage_id = \is_string($sid) || is_numeric($sid) ? (string) $sid : null;
        $d->description = \is_string($x = $request->get('description')) ? $x : null;
        $d->color = \is_string($x = $request->get('color')) ? $x : null;
        $st = $request->get('status');
        $d->status = \is_string($st) ? $st : (is_numeric($st) ? (string) $st : null);

        return $d;
    }
}
