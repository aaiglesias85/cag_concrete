<?php

namespace App\Service\Admin;

use App\Entity\Funcion;

/**
 * Groups permission rows for user/profile forms to mirror the admin sidebar order
 * (templates/admin/layout/menu.html.twig).
 */
final class FuncionPermissionUiGrouping
{
    /**
     * @var list<array{0: string, 1: string, 2: list<string>}>
     */
    private const GROUP_DEFINITIONS = [
        ['home', 'Home', ['home']],
        ['projects', 'Projects', ['company', 'projects', 'subcontractor', 'conc_vendor', 'concrete_class']],
        ['estimates', 'Estimates', [
            'estimate', 'project_stage', 'project_type', 'proposal_type', 'plan_status',
            'district', 'county', 'plan_downloading', 'note_estimate_item',
        ]],
        ['data_tracking', 'Data Tracking', ['schedule', 'data_tracking']],
        ['accounting', 'Accounting', ['invoice', 'payment', 'override_payment']],
        ['reports', 'Reports', ['reporte_subcontractor', 'reporte_employee']],
        ['rrhh', 'RRHH', ['race', 'employee_rrhh']],
        ['libraries', 'Libraries', [
            'unit', 'equation', 'item', 'inspectors', 'employees', 'employee_role',
            'materials', 'overhead', 'advertisement', 'reminder', 'holiday', 'tasks',
        ]],
        ['system', 'Settings', ['rol', 'users', 'notification', 'log']],
    ];

    /**
     * @param iterable<Funcion> $funciones
     *
     * @return list<array{grupoId: string, titulo: string, funciones: list<Funcion>}>
     */
    public function group(iterable $funciones): array
    {
        /** @var array<string, Funcion> $byUrl */
        $byUrl = [];
        foreach ($funciones as $f) {
            if ($f instanceof Funcion && $f->getUrl() !== null) {
                $byUrl[$f->getUrl()] = $f;
            }
        }

        $grouped = [];
        $assignedUrls = [];

        foreach (self::GROUP_DEFINITIONS as [$key, $title, $urls]) {
            $items = [];
            foreach ($urls as $url) {
                if (isset($byUrl[$url])) {
                    $items[] = $byUrl[$url];
                    $assignedUrls[$url] = true;
                }
            }
            if ($items !== []) {
                $grouped[] = [
                    'grupoId' => 'perm-mod-' . $key,
                    'titulo' => $title,
                    'funciones' => $items,
                ];
            }
        }

        $extras = [];
        foreach ($funciones as $f) {
            if (!$f instanceof Funcion || $f->getUrl() === null) {
                continue;
            }
            if (!isset($assignedUrls[$f->getUrl()])) {
                $extras[] = $f;
            }
        }
        if ($extras !== []) {
            usort($extras, static fn (Funcion $a, Funcion $b): int => ($a->getFuncionId() ?? 0) <=> ($b->getFuncionId() ?? 0));
            $grouped[] = [
                'grupoId' => 'perm-mod-other',
                'titulo' => 'Other',
                'funciones' => $extras,
            ];
        }

        return $grouped;
    }
}
