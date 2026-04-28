<?php

namespace App\Dto\Admin\Estimate;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

/** CSV de ids o vacío. */
final class EstimateEnviarQuotesRequest implements AdminHttpRequestDtoInterface
{
    public ?string $quote_ids = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $q = $request->get('quote_ids');
        $d->quote_ids = \is_string($q) || is_numeric($q) ? (string) $q : null;

        return $d;
    }
}
