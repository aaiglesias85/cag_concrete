<?php

namespace App\Dto\Admin\OverridePayment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class OverrideNotaUnpaidSalvarRequest
{
    #[Assert\NotBlank]
    public ?string $project_id = null;

    #[Assert\NotBlank]
    public ?string $fechaFin = null;

    #[Assert\Positive]
    public ?int $project_item_id = null;

    public string $notes = '';

    public ?string $override_unpaid_qty = null;

    public ?int $history_id = null;

    public ?string $override_unpaid_qty_previous = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $d->project_id = (string) $request->get('project_id', '');
        if ('' === trim($d->project_id)) {
            $d->project_id = null;
        }
        $d->fechaFin = (string) $request->get('fechaFin', '');
        if ('' === trim($d->fechaFin)) {
            $d->fechaFin = null;
        }
        $d->project_item_id = self::positiveIntOrNull($request->get('project_item_id'));
        $notes = $request->get('notes');
        $d->notes = \is_string($notes) ? $notes : '';
        $ouq = $request->get('override_unpaid_qty');
        $d->override_unpaid_qty = null !== $ouq && '' !== (string) $ouq ? (string) $ouq : null;
        $hir = $request->get('history_id');
        $d->history_id = is_numeric($hir) ? (int) $hir : null;
        if (null !== $d->history_id && $d->history_id <= 0) {
            $d->history_id = null;
        }
        $oup = $request->get('override_unpaid_qty_previous');
        $d->override_unpaid_qty_previous = null !== $oup && '' !== (string) $oup ? (string) $oup : null;

        return $d;
    }

    private static function positiveIntOrNull(mixed $v): ?int
    {
        if (null === $v || false === $v || '' === $v) {
            return null;
        }
        if (\is_int($v)) {
            return $v > 0 ? $v : null;
        }
        if (\is_string($v) && is_numeric($v)) {
            $i = (int) $v;

            return $i > 0 ? $i : null;
        }

        return null;
    }
}
