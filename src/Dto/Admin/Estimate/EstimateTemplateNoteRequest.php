<?php

namespace App\Dto\Admin\Estimate;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class EstimateTemplateNoteRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $estimate_id = null;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $estimate_note_item_id = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->estimate_id = self::pos($request->get('estimate_id'));
        $d->estimate_note_item_id = self::pos($request->get('estimate_note_item_id'));

        return $d;
    }

    private static function pos(mixed $v): ?int
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
