<?php

namespace App\Dto\Admin\Project;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ProjectArchivoNombreRequest
{
    #[Assert\NotBlank]
    public ?string $archivo = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $a = $request->get('archivo');
        $d->archivo = \is_string($a) ? $a : (is_numeric($a) ? (string) $a : null);

        return $d;
    }
}
