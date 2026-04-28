<?php

namespace App\Dto\Admin\Advertisement;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class AdvertisementSalvarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $title = null;

    public ?string $description = null;

    #[Assert\NotBlank]
    public ?string $status = null;

    public ?string $start_date = null;

    public ?string $end_date = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->title = \is_string($x = $request->get('title')) ? $x : null;
        $d->description = \is_string($x = $request->get('description')) ? $x : null;
        $st = $request->get('status');
        $d->status = \is_string($st) ? $st : (is_numeric($st) ? (string) $st : null);
        $d->start_date = \is_string($x = $request->get('start_date')) ? $x : null;
        $d->end_date = \is_string($x = $request->get('end_date')) ? $x : null;

        return $d;
    }
}
