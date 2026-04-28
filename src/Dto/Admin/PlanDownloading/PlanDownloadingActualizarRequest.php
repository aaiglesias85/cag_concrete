<?php

namespace App\Dto\Admin\PlanDownloading;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class PlanDownloadingActualizarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $plan_downloading_id = null;

    #[Assert\NotBlank]
    public ?string $description = null;

    #[Assert\NotBlank]
    public ?string $status = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $id = $request->get('plan_downloading_id');
        $d->plan_downloading_id = \is_string($id) || is_numeric($id) ? (string) $id : null;
        $d->description = \is_string($x = $request->get('description')) ? $x : null;
        $st = $request->get('status');
        $d->status = \is_string($st) ? $st : (is_numeric($st) ? (string) $st : null);

        return $d;
    }
}
