<?php

namespace App\Dto\Admin\Employee;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class EmployeeSalvarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $name = null;

    public ?string $hourly_rate = null;

    public ?string $role_id = null;

    public ?string $color = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->name = \is_string($x = $request->get('name')) ? $x : null;
        $hr = $request->get('hourly_rate');
        $d->hourly_rate = \is_string($hr) || is_numeric($hr) ? (string) $hr : null;
        $rid = $request->get('role_id');
        $d->role_id = \is_string($rid) || is_numeric($rid) ? (string) $rid : null;
        $d->color = \is_string($x = $request->get('color')) ? $x : null;

        return $d;
    }
}
