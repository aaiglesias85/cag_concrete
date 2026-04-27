<?php

namespace App\Dto\Admin\Estimate;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class EstimateArchivosStringRequest
{
    #[Assert\NotBlank]
    public ?string $archivos = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $a = $request->get('archivos');
        $d->archivos = \is_string($a) ? $a : (is_numeric($a) ? (string) $a : null);

        return $d;
    }
}
