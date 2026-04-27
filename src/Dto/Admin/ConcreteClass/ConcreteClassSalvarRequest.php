<?php

namespace App\Dto\Admin\ConcreteClass;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ConcreteClassSalvarRequest
{
    public ?string $concrete_class_id = null;

    #[Assert\NotBlank]
    public ?string $name = null;

    #[Assert\NotBlank]
    public ?string $status = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $id = $request->get('concrete_class_id');
        $d->concrete_class_id = \is_string($id) || is_numeric($id) ? (string) $id : null;
        $d->name = \is_string($x = $request->get('name')) ? $x : null;
        $st = $request->get('status');
        $d->status = \is_string($st) ? $st : (is_numeric($st) ? (string) $st : null);

        return $d;
    }
}
