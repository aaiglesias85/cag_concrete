<?php

namespace App\Dto\Admin\Project;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ProjectArchivosStringRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $archivos = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $a = $request->get('archivos');
        $d->archivos = \is_string($a) ? $a : (is_numeric($a) ? (string) $a : null);

        return $d;
    }
}
