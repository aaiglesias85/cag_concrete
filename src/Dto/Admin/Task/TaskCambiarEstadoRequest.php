<?php

namespace App\Dto\Admin\Task;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class TaskCambiarEstadoRequest
{
    #[Assert\NotBlank(message: 'Task id is required.')]
    #[Assert\Positive]
    public ?int $task_id = null;

    #[Assert\NotBlank]
    public ?string $status = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $d->task_id = self::positiveIntOrNull($request->get('task_id'));
        $d->status = \is_string($x = $request->get('status')) ? $x : null;

        return $d;
    }

    private static function positiveIntOrNull(mixed $v): ?int
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
