<?php

namespace App\Security\Attribute;

use App\Security\AdminPermission;

/**
 * Ejecuta login + comprobación de permiso antes de la acción (redirige como AdminAccessService).
 * Por defecto exige permiso "ver".
 *
 * Ejemplos:
 *  - HTML: #[RequireAdminPermission(FunctionId::HOME)]
 *  - JSON (401/403 sin redirect): #[RequireAdminPermission(FunctionId::HOLIDAY, AdminPermission::Add, jsonOnDenied: true)]
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class RequireAdminPermission
{
    public function __construct(
        public readonly int $functionId,
        public readonly AdminPermission $permission = AdminPermission::View,
        public readonly bool $jsonOnDenied = false,
    ) {
    }
}
