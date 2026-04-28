<?php

namespace App\Dto\Admin\Schedule;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ScheduleClonarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $schedules_id = null;

    public ?string $highpriority = null;

    #[Assert\NotBlank]
    public ?string $date_start = null;

    #[Assert\NotBlank]
    public ?string $date_stop = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $sid = $request->get('schedules_id');
        $d->schedules_id = \is_string($sid) || is_numeric($sid) ? (string) $sid : null;
        $hp = $request->get('highpriority');
        $d->highpriority = \is_string($hp) || is_numeric($hp) ? (string) $hp : null;
        $d->date_start = \is_string($x = $request->get('date_start')) ? $x : null;
        $d->date_stop = \is_string($x = $request->get('date_stop')) ? $x : null;

        return $d;
    }
}
