<?php

namespace App\Dto\Admin\Usuario;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use App\Http\DataTablesHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * @phpstan-type ParsedDt array{draw:int,start:int,length:int,search:string,orderField:string,orderDir:'asc'|'desc',columns:array,raw:array}
 */
final class UsuarioListarRequest implements AdminHttpRequestDtoInterface
{
    /** @var ParsedDt */
    public array $dt;

    public mixed $perfil_id = null;

    public mixed $estado = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['id', 'email', 'nombre', 'apellidos', 'perfil', 'habilitado'],
            defaultOrderField: 'nombre'
        );
        $d->perfil_id = $request->get('perfil_id');
        $d->estado = $request->get('estado');

        return $d;
    }
}
