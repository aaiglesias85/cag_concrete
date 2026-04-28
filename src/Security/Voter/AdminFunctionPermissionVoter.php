<?php

namespace App\Security\Voter;

use App\Service\Admin\AdminAccessService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Autorización por función del panel admin usando los mismos flags en BD que AdminAccessService
 * (ver / agregar / editar / eliminar).
 *
 * Uso: $this->isGranted(self::VIEW, FunctionId::HOME) con usuario autenticado como Usuario.
 * Para rutas HTML que deben redirigir a "denegado", combinar con exigirUsuarioOlogin y redirect manual.
 */
final class AdminFunctionPermissionVoter extends Voter
{
    public const VIEW = 'ADMIN_FUNCTION_PERM_VIEW';

    public const ADD = 'ADMIN_FUNCTION_PERM_ADD';

    public const EDIT = 'ADMIN_FUNCTION_PERM_EDIT';

    public const DELETE = 'ADMIN_FUNCTION_PERM_DELETE';

    /** @var list<string> */
    private const ATTRIBUTES = [self::VIEW, self::ADD, self::EDIT, self::DELETE];

    public function __construct(
        private readonly AdminAccessService $adminAccess,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, self::ATTRIBUTES, true) && \is_int($subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => $this->adminAccess->usuarioPuedeVer($user, $subject),
            self::ADD => $this->adminAccess->usuarioPuedeAgregar($user, $subject),
            self::EDIT => $this->adminAccess->usuarioPuedeEditar($user, $subject),
            self::DELETE => $this->adminAccess->usuarioPuedeEliminar($user, $subject),
            default => false,
        };
    }
}
