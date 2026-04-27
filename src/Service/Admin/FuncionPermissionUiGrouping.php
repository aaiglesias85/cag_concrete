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
        // 0. Home
        ['home', 'Home', ['home']],

        // 1. Estimating (sin company: solo en Project Onboarding; sin duplicar filas)
        ['estimating', 'Estimating', [
            'estimate',
            'item', 'equation', 'unit', 'project_stage', 'project_type', 'proposal_type',
            'plan_status', 'plan_downloading', 'district', 'county', 'note_estimate_item',
        ]],

        // 2. Project Onboarding
        ['project_onboarding', 'Project Onboarding', [
            'contract', 'company', 'projects', 'subcontractor', 'concrete_class',
        ]],

        // 3. Project Management
        ['project_management', 'Project Management', [
            'schedule', 'data_tracking', 'inspectors', 'holiday',
        ]],

        // 4. Accounting
        ['accounting_receivable', 'Accounting - Receivable', [
            'invoice', 'payment', 'override_payment',
        ]],
        ['accounting_payable', 'Accounting - Payable', [
            'reporte_subcontractor', 'conc_vendor',
        ]],
        ['accounting_libraries', 'Accounting - Libraries', [
            'overhead', 'materials',
        ]],

        // 5. HR
        ['hr', 'HR', [
            'employee_rrhh', 'certified_payroll', 'reporte_employee',
            'race', 'employees', 'employee_role', 'crew',
        ]],

        // 6. Admin (tasks bajo Anuncios, alineado al menú lateral)
        ['admin', 'Admin', [
            'rol', 'users', 'advertisement', 'tasks',
        ]],

        // User Settings (top right)
        ['user_settings', 'User Settings', [
            'reminder', 'notification', 'log',
        ]],
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
            if (null !== $f->getUrl()) {
                $byUrl[$f->getUrl()] = $f;
            }
        }

        $grouped = [];
        $assignedUrls = [];

        foreach (self::GROUP_DEFINITIONS as [$key, $title, $urls]) {
            $items = [];
            foreach ($urls as $url) {
                if (!isset($byUrl[$url]) || isset($assignedUrls[$url])) {
                    continue;
                }
                $items[] = $byUrl[$url];
                $assignedUrls[$url] = true;
            }
            if ([] !== $items) {
                $grouped[] = [
                    'grupoId' => 'perm-mod-'.$key,
                    'titulo' => $title,
                    'funciones' => $items,
                ];
            }
        }

        $extras = [];
        foreach ($funciones as $f) {
            if (null === $f->getUrl()) {
                continue;
            }
            if (!isset($assignedUrls[$f->getUrl()])) {
                $extras[] = $f;
            }
        }
        if ([] !== $extras) {
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
