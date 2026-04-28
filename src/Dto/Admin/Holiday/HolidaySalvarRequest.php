<?php

namespace App\Dto\Admin\Holiday;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class HolidaySalvarRequest implements AdminHttpRequestDtoInterface
{
    public ?string $holiday_id = null;

    #[Assert\NotBlank]
    public ?string $day = null;

    #[Assert\NotBlank]
    public ?string $description = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $hid = $request->get('holiday_id');
        $d->holiday_id = \is_string($hid) || is_numeric($hid) ? (string) $hid : null;
        $d->day = \is_string($x = $request->get('day')) ? $x : null;
        $d->description = \is_string($x = $request->get('description')) ? $x : null;

        return $d;
    }
}
