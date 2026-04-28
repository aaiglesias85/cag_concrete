<?php

namespace App\Dto\Admin\Task;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class TaskSalvarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $description = null;

    #[Assert\NotBlank]
    public ?string $status = null;

    public ?string $due_day = null;

    public ?string $usuario_id = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->description = \is_string($x = $request->get('description')) ? $x : null;
        $st = $request->get('status');
        $d->status = \is_string($st) ? $st : (is_numeric($st) ? (string) $st : null);
        $dd = $request->get('due_day');
        $d->due_day = \is_string($dd) || is_numeric($dd) ? (string) $dd : null;
        $uid = $request->get('usuario_id');
        $d->usuario_id = \is_string($uid) || is_numeric($uid) ? (string) $uid : null;

        return $d;
    }
}
