<?php

namespace App\Dto\Admin\Material;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use App\Http\DataTablesHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * @phpstan-type ParsedDt array{draw:int,start:int,length:int,search:string,orderField:string,orderDir:'asc'|'desc',columns:array,raw:array}
 */
final class MaterialListarRequest implements AdminHttpRequestDtoInterface
{
    /** @var ParsedDt */
    public array $dt;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['id', 'name', 'unit', 'price'],
            defaultOrderField: 'name'
        );

        return $d;
    }
}
