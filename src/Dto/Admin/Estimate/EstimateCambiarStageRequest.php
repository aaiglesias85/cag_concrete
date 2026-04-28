<?php

namespace App\Dto\Admin\Estimate;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class EstimateCambiarStageRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $estimate_id = null;

    #[Assert\NotBlank]
    public ?string $stage_id = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->estimate_id = self::posE($request->get('estimate_id'));
        $sid = $request->get('stage_id');
        $d->stage_id = \is_string($sid) || is_numeric($sid) ? (string) $sid : null;

        return $d;
    }

    private static function posE(mixed $v): ?int
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
