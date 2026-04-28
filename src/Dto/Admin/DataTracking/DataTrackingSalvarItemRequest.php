<?php

namespace App\Dto\Admin\DataTracking;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class DataTrackingSalvarItemRequest implements AdminHttpRequestDtoInterface
{
    public ?string $data_tracking_id = null;

    #[Assert\NotBlank]
    public ?string $project_id = null;

    #[Assert\NotBlank]
    public ?string $date = null;

    public ?string $data_tracking_item_id = null;

    #[Assert\NotBlank]
    public ?string $item_id = null;

    #[Assert\NotBlank]
    public ?string $quantity = null;

    public ?string $punch_quantity = null;

    public ?string $notes = null;

    public ?string $price = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->data_tracking_id = self::scalarToStr($request->get('data_tracking_id'));
        $d->project_id = self::strNotEmpty($request->get('project_id'));
        $d->date = self::strNotEmpty($request->get('date'));
        $d->data_tracking_item_id = self::scalarToStr($request->get('data_tracking_item_id'));
        $d->item_id = self::strNotEmpty($request->get('item_id'));
        $d->quantity = self::strNotEmpty($request->get('quantity'));
        $d->punch_quantity = self::scalarToStr($request->get('punch_quantity'));
        $d->notes = self::scalarToStr($request->get('notes'));
        $d->price = self::scalarToStr($request->get('price'));

        return $d;
    }

    private static function strNotEmpty(mixed $v): ?string
    {
        if (null === $v || false === $v || '' === $v) {
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

    private static function scalarToStr(mixed $v): ?string
    {
        if (null === $v || false === $v || '' === $v) {
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
