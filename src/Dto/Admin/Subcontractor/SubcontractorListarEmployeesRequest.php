<?php

namespace App\Dto\Admin\Subcontractor;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use App\Http\DataTablesHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * @phpstan-type ParsedDt array{draw:int,start:int,length:int,search:string,orderField:string,orderDir:'asc'|'desc',columns:array,raw:array}
 */
final class SubcontractorListarEmployeesRequest implements AdminHttpRequestDtoInterface
{
    /** @var ParsedDt */
    public array $dt;

    public mixed $subcontractor_id = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['id', 'name', 'hourlyRate', 'position'],
            defaultOrderField: 'name'
        );
        $d->subcontractor_id = $request->get('subcontractor_id');

        return $d;
    }
}
