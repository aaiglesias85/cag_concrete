<?php

namespace App\Dto\Admin\DataTracking;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carga masiva de un registro de data tracking (JSON vía string en conc_vendors, items, etc.).
 */
final class DataTrackingSalvarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $project_id = null;

    #[Assert\NotBlank]
    public ?string $date = null;

    public ?string $inspector_id = null;

    public ?string $station_number = null;

    public ?string $measured_by = null;

    public ?string $conc_vendor = null;

    public ?string $conc_price = null;

    public ?string $crew_lead = null;

    public ?string $notes = null;

    public ?string $other_materials = null;

    public ?string $total_conc_used = null;

    public ?string $total_stamps = null;

    public ?string $total_people = null;

    public ?string $overhead_price_id = null;

    public ?string $color_used = null;

    public ?string $color_price = null;

    public ?string $conc_vendors = null;

    public ?string $items = null;

    public ?string $labor = null;

    public ?string $materials = null;

    public ?string $subcontracts = null;

    public ?string $archivos = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->project_id = self::strOrNull($request->get('project_id'));
        $d->date = self::strOrNull($request->get('date'));
        $d->inspector_id = self::strOrNull($request->get('inspector_id'));
        $d->station_number = self::strOrNull($request->get('station_number'));
        $d->measured_by = self::strOrNull($request->get('measured_by'));
        $d->conc_vendor = self::strOrNull($request->get('conc_vendor'));
        $d->conc_price = self::strOrNull($request->get('conc_price'));
        $d->crew_lead = self::strOrNull($request->get('crew_lead'));
        $d->notes = self::strOrNull($request->get('notes'));
        $d->other_materials = self::strOrNull($request->get('other_materials'));
        $d->total_conc_used = self::strOrNull($request->get('total_conc_used'));
        $d->total_stamps = self::strOrNull($request->get('total_stamps'));
        $d->total_people = self::strOrNull($request->get('total_people'));
        $d->overhead_price_id = self::strOrNull($request->get('overhead_price_id'));
        $d->color_used = self::strOrNull($request->get('color_used'));
        $d->color_price = self::strOrNull($request->get('color_price'));
        $d->conc_vendors = \is_string($x = $request->get('conc_vendors')) ? $x : null;
        $d->items = \is_string($x = $request->get('items')) ? $x : null;
        $d->labor = \is_string($x = $request->get('labor')) ? $x : null;
        $d->materials = \is_string($x = $request->get('materials')) ? $x : null;
        $d->subcontracts = \is_string($x = $request->get('subcontracts')) ? $x : null;
        $d->archivos = \is_string($x = $request->get('archivos')) ? $x : null;

        return $d;
    }

    public static function fromActualizarRequest(DataTrackingActualizarRequest $a): self
    {
        $d = new self();
        $d->project_id = $a->project_id;
        $d->date = $a->date;
        $d->inspector_id = $a->inspector_id;
        $d->station_number = $a->station_number;
        $d->measured_by = $a->measured_by;
        $d->conc_vendor = $a->conc_vendor;
        $d->conc_price = $a->conc_price;
        $d->crew_lead = $a->crew_lead;
        $d->notes = $a->notes;
        $d->other_materials = $a->other_materials;
        $d->total_conc_used = $a->total_conc_used;
        $d->total_stamps = $a->total_stamps;
        $d->total_people = $a->total_people;
        $d->overhead_price_id = $a->overhead_price_id;
        $d->color_used = $a->color_used;
        $d->color_price = $a->color_price;
        $d->conc_vendors = $a->conc_vendors;
        $d->items = $a->items;
        $d->labor = $a->labor;
        $d->materials = $a->materials;
        $d->subcontracts = $a->subcontracts;
        $d->archivos = $a->archivos;

        return $d;
    }

    private static function strOrNull(mixed $v): ?string
    {
        if (null === $v || false === $v) {
            return null;
        }
        if (\is_string($v)) {
            return $v;
        }
        if (is_numeric($v)) {
            return (string) $v;
        }

        return null;
    }
}
