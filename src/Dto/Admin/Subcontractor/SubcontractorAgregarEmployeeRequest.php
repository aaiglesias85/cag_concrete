<?php

namespace App\Dto\Admin\Subcontractor;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class SubcontractorAgregarEmployeeRequest
{
    public ?string $employee_id = null;

    #[Assert\NotBlank]
    public ?string $subcontractor_id = null;

    #[Assert\NotBlank]
    public ?string $name = null;

    public ?string $hourly_rate = null;

    public ?string $position = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $eid = $request->get('employee_id');
        $d->employee_id = \is_string($eid) || is_numeric($eid) ? (string) $eid : null;
        $sid = $request->get('subcontractor_id');
        $d->subcontractor_id = \is_string($sid) || is_numeric($sid) ? (string) $sid : null;
        $d->name = \is_string($x = $request->get('name')) ? $x : null;
        $hr = $request->get('hourly_rate');
        $d->hourly_rate = \is_string($hr) || is_numeric($hr) ? (string) $hr : null;
        $d->position = \is_string($x = $request->get('position')) ? $x : null;

        return $d;
    }
}
