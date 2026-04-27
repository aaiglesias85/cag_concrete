<?php

namespace App\Dto\Admin\Estimate;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class EstimateSalvarQuoteRequest
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $estimate_id = null;

    public ?string $quote_id = null;

    #[Assert\NotBlank]
    public ?string $name = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $d->estimate_id = self::pos($request->get('estimate_id'));
        $q = $request->get('quote_id');
        $d->quote_id = \is_string($q) || is_numeric($q) ? (string) $q : null;
        $n = $request->get('name');
        $d->name = \is_string($n) ? $n : null;

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
