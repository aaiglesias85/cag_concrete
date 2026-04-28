<?php

namespace App\Dto\Admin\OverridePayment;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class OverrideNotaUnpaidEliminarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $project_id = null;

    #[Assert\Positive(message: 'project_item_id is required.')]
    public ?int $project_item_id = null;

    #[Assert\Positive(message: 'history_id is required.')]
    public ?int $history_id = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $pid = $request->get('project_id');
        $d->project_id = null !== $pid && '' !== (string) $pid ? (string) $pid : null;
        $d->project_item_id = self::positiveIntOrNull($request->get('project_item_id'));
        $d->history_id = self::positiveIntOrNull($request->get('history_id'));

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
