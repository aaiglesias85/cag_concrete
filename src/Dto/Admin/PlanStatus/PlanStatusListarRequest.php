<?php

namespace App\Dto\Admin\PlanStatus;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use App\Http\DataTablesHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * @phpstan-type ParsedDt array{draw:int,start:int,length:int,search:string,orderField:string,orderDir:'asc'|'desc',columns:array,raw:array}
 */
final class PlanStatusListarRequest implements AdminHttpRequestDtoInterface
{
    /** @var ParsedDt */
    public array $dt;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['id', 'description', 'status'],
            defaultOrderField: 'description'
        );

        return $d;
    }
}
