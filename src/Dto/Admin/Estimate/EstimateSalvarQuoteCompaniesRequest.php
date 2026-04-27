<?php

namespace App\Dto\Admin\Estimate;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class EstimateSalvarQuoteCompaniesRequest
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $quote_id = null;

    /** String CSV, array, o null — el controlador hace `explode` / array como antes. */
    public mixed $company_ids = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $d->quote_id = self::pos($request->get('quote_id'));
        $d->company_ids = $request->get('company_ids');

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
