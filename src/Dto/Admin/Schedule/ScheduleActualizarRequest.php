<?php

namespace App\Dto\Admin\Schedule;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ScheduleActualizarRequest
{
    #[Assert\NotBlank]
    public ?string $schedule_id = null;

    #[Assert\NotBlank]
    public ?string $project_id = null;

    public ?string $project_contact_id = null;

    #[Assert\NotBlank]
    public ?string $description = null;

    public ?string $location = null;

    public ?string $latitud = null;

    public ?string $longitud = null;

    public ?string $vendor_id = null;

    public ?string $concrete_vendor_contacts_id = null;

    public ?string $day = null;

    public ?string $hour = null;

    #[Assert\NotBlank]
    public ?string $quantity = null;

    public ?string $notes = null;

    public ?string $highpriority = null;

    public ?string $employees_id = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $d->schedule_id = self::s($request->get('schedule_id'));
        $d->project_id = self::s($request->get('project_id'));
        $d->project_contact_id = self::s($request->get('project_contact_id'));
        $d->description = self::s($request->get('description'));
        $d->location = self::s($request->get('location'));
        $d->latitud = self::s($request->get('latitud'));
        $d->longitud = self::s($request->get('longitud'));
        $d->vendor_id = self::s($request->get('vendor_id'));
        $d->concrete_vendor_contacts_id = self::s($request->get('concrete_vendor_contacts_id'));
        $d->day = self::s($request->get('day'));
        $d->hour = self::s($request->get('hour'));
        $d->quantity = self::s($request->get('quantity'));
        $d->notes = self::s($request->get('notes'));
        $d->highpriority = self::s($request->get('highpriority'));
        $d->employees_id = self::s($request->get('employees_id'));

        return $d;
    }

    private static function s(mixed $v): ?string
    {
        if (null === $v || false === $v) {
            return null;
        }
        if (\is_string($v) || is_numeric($v)) {
            return (string) $v;
        }

        return null;
    }
}
