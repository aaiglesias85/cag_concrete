<?php

namespace App\Dto\Admin\Holiday;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class HolidaySalvarRequest
{
    public ?string $holiday_id = null;

    #[Assert\NotBlank]
    public ?string $day = null;

    #[Assert\NotBlank]
    public ?string $description = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $id = $request->get('holiday_id');
        $d->holiday_id = \is_string($id) || is_numeric($id) ? (string) $id : null;
        $d->day = \is_string($x = $request->get('day')) ? $x : null;
        $d->description = \is_string($x = $request->get('description')) ? $x : null;

        return $d;
    }
}
