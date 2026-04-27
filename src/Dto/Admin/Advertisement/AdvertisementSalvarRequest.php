<?php

namespace App\Dto\Admin\Advertisement;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class AdvertisementSalvarRequest
{
    public ?string $advertisement_id = null;

    #[Assert\NotBlank]
    public ?string $title = null;

    public ?string $description = null;

    #[Assert\NotBlank]
    public ?string $status = null;

    public ?string $start_date = null;

    public ?string $end_date = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $id = $request->get('advertisement_id');
        $d->advertisement_id = \is_string($id) || is_numeric($id) ? (string) $id : null;
        $d->title = \is_string($x = $request->get('title')) ? $x : null;
        $d->description = \is_string($x = $request->get('description')) ? $x : null;
        $st = $request->get('status');
        $d->status = \is_string($st) ? $st : (is_numeric($st) ? (string) $st : null);
        $d->start_date = \is_string($x = $request->get('start_date')) ? $x : null;
        $d->end_date = \is_string($x = $request->get('end_date')) ? $x : null;

        return $d;
    }
}
