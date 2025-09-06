<?php
// src/Http/DataTablesHelper.php
namespace App\Http;

use Symfony\Component\HttpFoundation\Request;

final class DataTablesHelper
{
    /**
     * Parsea parámetros de jQuery DataTables (server-side).
     *
     * @param Request $request
     * @param string[] $allowedOrderFields Lista blanca de campos ordenables (p.ej. ['id','nombre'])
     * @param string $defaultOrderField   Campo por defecto si el pedido no es válido
     * @return array{
     *   draw:int,
     *   start:int,
     *   length:int,
     *   search:string,
     *   orderField:string,
     *   orderDir:'asc'|'desc',
     *   columns:array<int, array{
     *     data: string|null,
     *     name: string|null,
     *     searchable: bool,
     *     orderable: bool,
     *     search: array{value:string, regex:bool}
     *   }>,
     *   raw: array
     * }
     */
    public static function parse(Request $request, array $allowedOrderFields = ['id'], string $defaultOrderField = 'id'): array
    {
        // DataTables puede llegar por GET o POST
        $params = $request->isMethod('POST') ? $request->request->all() : $request->query->all();

        // draw
        $draw = (int)($params['draw'] ?? 0);

        // paginación
        $start  = (int)($params['start']  ?? 0);
        $length = (int)($params['length'] ?? 10);
        if ($length < 0) {
            $length = PHP_INT_MAX; // -1 = sin límite
        }

        // búsqueda global
        $searchValue = '';
        if (isset($params['search']) && is_array($params['search'])) {
            $searchValue = trim((string)($params['search']['value'] ?? ''));
        }

        // columnas y orden
        $columns = [];
        if (isset($params['columns']) && is_array($params['columns'])) {
            foreach ($params['columns'] as $c) {
                $columns[] = [
                    'data'       => (isset($c['data']) && is_string($c['data']) && $c['data'] !== '') ? $c['data'] : null,
                    'name'       => (isset($c['name']) && is_string($c['name']) && $c['name'] !== '') ? $c['name'] : null,
                    'searchable' => filter_var($c['searchable'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'orderable'  => filter_var($c['orderable']  ?? false, FILTER_VALIDATE_BOOLEAN),
                    'search'     => [
                        'value' => isset($c['search']['value']) ? (string)$c['search']['value'] : '',
                        'regex' => filter_var($c['search']['regex'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    ],
                ];
            }
        }

        $order  = $params['order'] ?? [];
        $orderColIndex = (int)($order[0]['column'] ?? 0);
        $orderDirRaw   = strtolower((string)($order[0]['dir'] ?? 'asc'));
        $orderDir      = $orderDirRaw === 'desc' ? 'desc' : 'asc';

        // Resuelve nombre del campo a ordenar desde columns[*][name] o [data]
        $orderField = $defaultOrderField;
        if (isset($columns[$orderColIndex])) {

            $candidate = $columns[$orderColIndex]['name'];
            if ($candidate === null || $candidate === '') {
                $candidate = $columns[$orderColIndex]['data'];
            }

            if (is_string($candidate) && $candidate !== '') {
                $orderField = $candidate;
            }
        }

        // Lista blanca contra inyección
        if (!in_array($orderField, $allowedOrderFields, true)) {
            $orderField = $defaultOrderField;
        }

        return [
            'draw'       => $draw,
            'start'      => $start,
            'length'     => $length,
            'search'     => $searchValue,
            'orderField' => $orderField,
            'orderDir'   => $orderDir,
            'columns'    => $columns,
            'raw'        => $params, // por si necesitas algo específico después
        ];
    }
}
