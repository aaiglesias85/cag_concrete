<?php

namespace App\Service\Admin;

use App\Entity\PermisoUsuario;
use App\Entity\Usuario;
use App\Repository\PermisoUsuarioRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Acceso reutilizable para el panel /admin: usuario autenticado como entidad Usuario
 * y comprobación de permisos por función (misma lógica que App\Utils\Base::BuscarPermiso).
 *
 * Uso: inyectar en el controlador y, al inicio de la acción:
 *  - exigirUsuarioOlogin: rutas que solo requieren sesión válida
 *  - exigirUsuarioYPermisoVer: listados principales (equivalente a "tiene registro y puede ver")
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

        $permisos = $this->buscarPermisosMismoBase($gate->getUsuarioId(), $functionId);
        if (\count($permisos) > 0 && true === $permisos[0]['ver']) {
            return ['usuario' => $gate, 'permisos' => $permisos];
        }

        return $this->redirect('denegado');
    }

    private function redirect(string $route): RedirectResponse
    {
        return new RedirectResponse(
            $this->urlGenerator->generate($route),
            302
        );
    }

    /**
     * Replica el formato de App\Utils\Base::BuscarPermiso (sin acoplar al contenedor de Base).
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
