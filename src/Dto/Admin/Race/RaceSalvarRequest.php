<?php

namespace App\Dto\Admin\Race;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class RaceSalvarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $code = null;

    #[Assert\NotBlank]
    public ?string $description = null;

    /** Optional in UI (labels: required only on code & description). Max length matches DB. */
    #[Assert\Length(max: 255)]
    public ?string $classification = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->code = \is_string($x = $request->get('code')) ? $x : null;
        $d->description = \is_string($x = $request->get('description')) ? $x : null;
        $d->classification = \is_string($x = $request->get('classification')) ? $x : null;

        return $d;
    }
}
