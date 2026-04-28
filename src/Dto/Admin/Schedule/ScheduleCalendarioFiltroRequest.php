<?php

namespace App\Dto\Admin\Schedule;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

final class ScheduleCalendarioFiltroRequest implements AdminHttpRequestDtoInterface
{
    public ?string $search = null;

    public ?string $project_id = null;

    public ?string $vendor_id = null;

    public ?string $fecha_inicial = null;

    public ?string $fecha_fin = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->search = self::sn($request->get('search'));
        $d->project_id = self::sn($request->get('project_id'));
        $d->vendor_id = self::sn($request->get('vendor_id'));
        $d->fecha_inicial = self::sn($request->get('fecha_inicial'));
        $d->fecha_fin = self::sn($request->get('fecha_fin'));

        return $d;
    }

    private static function sn(mixed $v): ?string
    {
        if (null === $v) {
            return null;
        }
        if (\is_string($v) || is_numeric($v)) {
            return (string) $v;
        }

        return null;
    }
}
