<?php

namespace App\Dto\Admin;

use Symfony\Component\HttpFoundation\Request;

/**
 * Contrato para DTOs del panel admin construidos desde la petición HTTP,
 * resolubles por AdminHttpRequestDtoValueResolver.
 */
interface AdminHttpRequestDtoInterface
{
    public static function fromHttpRequest(Request $request): static;
}
