<?php

namespace App\Service\Admin;

use App\Entity\PermisoUsuario;
use App\Entity\Usuario;
use App\Repository\PermisoUsuarioRepository;
use App\Security\AdminPermission;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Acceso reutilizable para el panel /admin: usuario autenticado como entidad Usuario
 * y comprobación de permisos por función (misma lógica que App\Service\Base\Base::BuscarPermiso).
 *
 * Uso: inyectar en el controlador y, al inicio de la acción:
 *  - exigirUsuarioOlogin: rutas que solo requieren sesión válida
 *  - exigirUsuarioYPermisoVer: listados principales (equivalente a "tiene registro y puede ver")
 *  - requirePermission: login + ver/agregar/editar/eliminar en una llamada (también usado por #[RequireAdminPermission])
 */
class AdminAccessService
{
    public function __construct(
        private readonly PermisoUsuarioRepository $permisoUsuarioRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @return Usuario|RedirectResponse redirige a login o denegado si el usuario no es adecuado
     */
    public function exigirUsuarioOlogin(?UserInterface $user): Usuario|RedirectResponse
    {
        if (null === $user) {
            return $this->redirect('login');
        }
        if (!$user instanceof Usuario) {
            return $this->redirect('denegado');
        }

        return $user;
    }

    /**
     * Sesión válida como Usuario + permiso sobre la función (misma BD que el voter). Devuelve redirect a login o denegado.
     */
    public function requirePermission(?UserInterface $user, int $functionId, AdminPermission $permission): Usuario|RedirectResponse
    {
        $gate = $this->exigirUsuarioOlogin($user);
        if ($gate instanceof RedirectResponse) {
            return $gate;
        }

        $allowed = match ($permission) {
            AdminPermission::View => $this->usuarioPuedeVer($gate, $functionId),
            AdminPermission::Add => $this->usuarioPuedeAgregar($gate, $functionId),
            AdminPermission::Edit => $this->usuarioPuedeEditar($gate, $functionId),
            AdminPermission::Delete => $this->usuarioPuedeEliminar($gate, $functionId),
        };

        if (!$allowed) {
            return $this->redirect('denegado');
        }

        return $gate;
    }

    /**
     * Misma lógica habitual en controladores: sesión, tipo Usuario y fila de permiso con "ver" activo.
     *
     * @return array{usuario: Usuario, permisos: array<int, array<string, mixed>>}|RedirectResponse
     */
    public function exigirUsuarioYPermisoVer(?UserInterface $user, int $functionId): array|RedirectResponse
    {
        $gate = $this->exigirUsuarioOlogin($user);
        if ($gate instanceof RedirectResponse) {
            return $gate;
        }

        $row = $this->primeraFilaPermiso($gate, $functionId);
        if (null === $row || true !== $row['ver']) {
            return $this->redirect('denegado');
        }

        return ['usuario' => $gate, 'permisos' => [$row]];
    }

    /**
     * Comprobaciones usadas por AdminFunctionPermissionVoter (misma BD que BuscarPermiso).
     */
    public function usuarioPuedeVer(UserInterface $user, int $functionId): bool
    {
        $row = $this->primeraFilaPermiso($user, $functionId);

        return null !== $row && true === $row['ver'];
    }

    public function usuarioPuedeAgregar(UserInterface $user, int $functionId): bool
    {
        $row = $this->primeraFilaPermiso($user, $functionId);

        return null !== $row && true === $row['agregar'];
    }

    public function usuarioPuedeEditar(UserInterface $user, int $functionId): bool
    {
        $row = $this->primeraFilaPermiso($user, $functionId);

        return null !== $row && true === $row['editar'];
    }

    public function usuarioPuedeEliminar(UserInterface $user, int $functionId): bool
    {
        $row = $this->primeraFilaPermiso($user, $functionId);

        return null !== $row && true === $row['eliminar'];
    }

    /**
     * Primera fila de permiso del usuario para la función, o null si no hay registro.
     *
     * @return array<string, mixed>|null
     */
    private function primeraFilaPermiso(UserInterface $user, int $functionId): ?array
    {
        if (!$user instanceof Usuario) {
            return null;
        }
        $permisos = $this->buscarPermisosMismoBase($user->getUsuarioId(), $functionId);

        return \count($permisos) > 0 ? $permisos[0] : null;
    }

    private function redirect(string $route): RedirectResponse
    {
        return new RedirectResponse(
            $this->urlGenerator->generate($route),
            302
        );
    }

    /**
     * Replica el formato de App\Service\Base\Base::BuscarPermiso (sin acoplar al contenedor de Base).
     *
     * @return array<int, array<string, mixed>>
     */
    public function buscarPermisosMismoBase(int $usuarioId, int $functionId): array
    {
        $permiso = $this->permisoUsuarioRepository->BuscarPermisoUsuario($usuarioId, $functionId);
        if (null === $permiso) {
            return [];
        }

        return [$this->mapPermisoEntityARow($permiso)];
    }

    private function mapPermisoEntityARow(PermisoUsuario $permiso): array
    {
        $ver = $permiso->getVer();
        $agregar = $permiso->getAgregar();
        $editar = $permiso->getEditar();
        $eliminar = $permiso->getEliminar();

        return [
            'permiso_id' => $permiso->getPermisoId(),
            'funcion_id' => $permiso->getFuncion()->getFuncionId(),
            'ver' => (1 == $ver) ? true : false,
            'agregar' => (1 == $agregar) ? true : false,
            'editar' => (1 == $editar) ? true : false,
            'eliminar' => (1 == $eliminar) ? true : false,
            'todos' => (1 == $ver && 1 == $agregar && 1 == $editar && 1 == $eliminar) ? true : false,
        ];
    }
}
