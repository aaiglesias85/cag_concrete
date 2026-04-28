<?php

namespace App\Dto\Admin\Inspector;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class InspectorSalvarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $name = null;

    #[Assert\Email]
    public ?string $email = null;

    public ?string $phone = null;

    #[Assert\NotBlank]
    public ?string $status = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->name = \is_string($x = $request->get('name')) ? $x : null;
        $d->email = \is_string($x = $request->get('email')) ? $x : null;
        $d->phone = \is_string($x = $request->get('phone')) ? $x : null;
        $st = $request->get('status');
        $d->status = \is_string($st) ? $st : (is_numeric($st) ? (string) $st : null);

        return $d;
    }
}
