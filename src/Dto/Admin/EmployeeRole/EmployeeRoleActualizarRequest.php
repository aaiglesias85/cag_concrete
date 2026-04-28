<?php

namespace App\Dto\Admin\EmployeeRole;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class EmployeeRoleActualizarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $role_id = null;

    #[Assert\NotBlank]
    public ?string $description = null;

    #[Assert\NotBlank]
    public ?string $status = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $rid = $request->get('role_id');
        $d->role_id = \is_string($rid) || is_numeric($rid) ? (string) $rid : null;
        $d->description = \is_string($x = $request->get('description')) ? $x : null;
        $st = $request->get('status');
        $d->status = \is_string($st) ? $st : (is_numeric($st) ? (string) $st : null);

        return $d;
    }
}
