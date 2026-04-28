<?php

namespace App\Controller\Admin;

use App\Entity\Usuario;
use App\Security\AdminPermission;
use App\Service\Admin\AdminAccessService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Clase base opcional para el panel de administración: inyecta AdminAccessService
 * (login, tipo de usuario, permisos por función) para reutilizar la misma lógica
 * en todas las acciones.
 */
abstract class AbstractAdminController extends AbstractController
{
    public function __construct(
        protected AdminAccessService $adminAccess,
    ) {
    }

    /**
     * Login + permiso en una sola llamada (alternativa a #[RequireAdminPermission]).
     */
    protected function requirePermission(int $functionId, AdminPermission $permission = AdminPermission::View): Usuario|RedirectResponse
    {
        return $this->adminAccess->requirePermission($this->getUser(), $functionId, $permission);
    }

    /**
     * Igual que requirePermission pero para acciones JSON (sin redirect HTML).
     * Preferir `#[RequireAdminPermission(FunctionId::X, AdminPermission::Y, jsonOnDenied: true)]` en el método.
     */
    protected function requirePermissionOrJson403(int $functionId, AdminPermission $permission = AdminPermission::View): Usuario|JsonResponse
    {
        $r = $this->adminAccess->requirePermission($this->getUser(), $functionId, $permission);
        if ($r instanceof RedirectResponse) {
            $status = null === $this->getUser() ? Response::HTTP_UNAUTHORIZED : Response::HTTP_FORBIDDEN;

            return $this->json(['success' => false, 'error' => 'Access denied'], $status);
        }

        return $r;
    }

    /**
     * Usuario del panel tras #[RequireAdminPermission] (o requirePermission sin redirect).
     */
    protected function DevolverUsuario(): Usuario
    {
        $u = $this->getUser();
        if (!$u instanceof Usuario) {
            throw new \LogicException('Se esperaba App\Entity\Usuario; ¿falta #[RequireAdminPermission] o login?');
        }

        return $u;
    }
}
