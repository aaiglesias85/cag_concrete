<?php

namespace App\Dto\Admin\Reminder;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ReminderActualizarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $reminder_id = null;

    #[Assert\NotBlank]
    public ?string $day = null;

    #[Assert\NotBlank]
    public ?string $subject = null;

    public ?string $body = null;

    #[Assert\NotBlank]
    public ?string $status = null;

    public ?string $usuarios_id = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $rid = $request->get('reminder_id');
        $d->reminder_id = \is_string($rid) || is_numeric($rid) ? (string) $rid : null;
        $d->day = \is_string($x = $request->get('day')) ? $x : null;
        $d->subject = \is_string($x = $request->get('subject')) ? $x : null;
        $d->body = \is_string($x = $request->get('body')) ? $x : null;
        $st = $request->get('status');
        $d->status = \is_string($st) ? $st : (is_numeric($st) ? (string) $st : null);
        $d->usuarios_id = \is_string($x = $request->get('usuarios_id')) ? $x : null;

        return $d;
    }
}
