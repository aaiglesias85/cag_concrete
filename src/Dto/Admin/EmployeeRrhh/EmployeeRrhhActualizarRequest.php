<?php

namespace App\Dto\Admin\EmployeeRrhh;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class EmployeeRrhhActualizarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $employee_id = null;

    #[Assert\NotBlank]
    public ?string $name = null;

    public ?string $address = null;

    public ?string $phone = null;

    public ?string $cert_rate_type = null;

    public ?string $social_security_number = null;

    public ?string $apprentice_percentage = null;

    public ?string $work_code = null;

    public ?string $gender = null;

    public ?string $race_id = null;

    public ?string $date_hired = null;

    public ?string $date_terminated = null;

    public ?string $reason_terminated = null;

    public ?string $time_card_notes = null;

    public ?string $regular_rate_per_hour = null;

    public ?string $overtime_rate_per_hour = null;

    public ?string $special_rate_per_hour = null;

    public ?string $trade_licenses_info = null;

    public ?string $notes = null;

    public ?string $is_osha_10_certified = null;

    public ?string $is_veteran = null;

    #[Assert\NotBlank]
    public ?string $status = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $eid = $request->get('employee_id');
        $d->employee_id = \is_string($eid) || is_numeric($eid) ? (string) $eid : null;
        $d->name = \is_string($x = $request->get('name')) ? $x : null;
        $d->address = \is_string($x = $request->get('address')) ? $x : null;
        $d->phone = \is_string($x = $request->get('phone')) ? $x : null;
        $crt = $request->get('cert_rate_type');
        $d->cert_rate_type = \is_string($crt) || is_numeric($crt) ? (string) $crt : null;
        $d->social_security_number = \is_string($x = $request->get('social_security_number')) ? $x : null;
        $ap = $request->get('apprentice_percentage');
        $d->apprentice_percentage = \is_string($ap) || is_numeric($ap) ? (string) $ap : null;
        $d->work_code = \is_string($x = $request->get('work_code')) ? $x : null;
        $g = $request->get('gender');
        $d->gender = \is_string($g) || is_numeric($g) ? (string) $g : null;
        $rid = $request->get('race_id');
        $d->race_id = \is_string($rid) || is_numeric($rid) ? (string) $rid : null;
        $d->date_hired = \is_string($x = $request->get('date_hired')) ? $x : null;
        $d->date_terminated = \is_string($x = $request->get('date_terminated')) ? $x : null;
        $d->reason_terminated = \is_string($x = $request->get('reason_terminated')) ? $x : null;
        $d->time_card_notes = \is_string($x = $request->get('time_card_notes')) ? $x : null;
        $rrh = $request->get('regular_rate_per_hour');
        $d->regular_rate_per_hour = \is_string($rrh) || is_numeric($rrh) ? (string) $rrh : null;
        $orh = $request->get('overtime_rate_per_hour');
        $d->overtime_rate_per_hour = \is_string($orh) || is_numeric($orh) ? (string) $orh : null;
        $srh = $request->get('special_rate_per_hour');
        $d->special_rate_per_hour = \is_string($srh) || is_numeric($srh) ? (string) $srh : null;
        $d->trade_licenses_info = \is_string($x = $request->get('trade_licenses_info')) ? $x : null;
        $d->notes = \is_string($x = $request->get('notes')) ? $x : null;
        $os = $request->get('is_osha_10_certified');
        $d->is_osha_10_certified = \is_string($os) || is_numeric($os) ? (string) $os : null;
        $vet = $request->get('is_veteran');
        $d->is_veteran = \is_string($vet) || is_numeric($vet) ? (string) $vet : null;
        $st = $request->get('status');
        $d->status = \is_string($st) ? $st : (is_numeric($st) ? (string) $st : null);

        return $d;
    }
}
