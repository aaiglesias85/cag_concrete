<?php

namespace App\Dto\Admin\Usuario;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * salvar/actualizar usuario (admin). {@see UsuarioController::salvar}
 * permisos: string JSON (array of permission objects) como envía el front.
 */
final class UsuarioSalvarRequest
{
    public ?string $usuario_id = null;

    /** Campo de formulario `rol` (id de rol) */
    #[Assert\NotBlank(message: 'Role is required.')]
    public ?string $rol = null;

    public ?string $habilitado = null;

    public ?string $password = null;

    #[Assert\NotBlank]
    public ?string $nombre = null;

    #[Assert\NotBlank]
    public ?string $apellidos = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    /** JSON string (array) */
    public ?string $permisos = null;

    public ?string $telefono = null;

    public ?string $estimator = null;
    public ?string $bond = null;
    public ?string $retainage = null;
    public ?string $chat = null;

    public ?string $widget_access = null;

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        $isNew = null === $this->usuario_id || '' === (string) $this->usuario_id;
        if ($isNew) {
            if (null === $this->password || '' === (string) $this->password) {
                $context->buildViolation('Password is required for a new user.')
                    ->disableTranslation()
                    ->atPath('password')
                    ->addViolation();
            }
        }

        if (null === $this->permisos || '' === (string) $this->permisos) {
            $context->buildViolation('Permissions (JSON) are required.')
                ->disableTranslation()
                ->atPath('permisos')
                ->addViolation();

            return;
        }

        $permisosJson = json_decode($this->permisos, false, 512);
        if (JSON_ERROR_NONE !== json_last_error() || !\is_array($permisosJson)) {
            $context->buildViolation('Permissions must be a valid JSON array.')
                ->disableTranslation()
                ->atPath('permisos')
                ->addViolation();

            return;
        }

        if (null === $this->widget_access || '' === (string) $this->widget_access) {
            return;
        }

        $wa = json_decode($this->widget_access, true, 512);
        if (JSON_ERROR_NONE !== json_last_error() || !\is_array($wa)) {
            $context->buildViolation('Widget access must be a valid JSON array.')
                ->disableTranslation()
                ->atPath('widget_access')
                ->addViolation();
        }
    }

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $uid = $request->get('usuario_id');
        $d->usuario_id = \is_string($uid) || is_numeric($uid) ? (string) $uid : null;
        $d->rol = \is_string($r = $request->get('rol')) ? $r : (null === $r ? null : (string) $r);
        $d->habilitado = null !== $request->get('habilitado') && false !== $request->get('habilitado')
            ? (string) $request->get('habilitado') : null;
        $d->password = \is_string($p = $request->get('password')) ? $p : null;
        $d->nombre = \is_string($n = $request->get('nombre')) ? $n : null;
        $d->apellidos = \is_string($a = $request->get('apellidos')) ? $a : null;
        $d->email = \is_string($e = $request->get('email')) ? $e : null;
        $d->permisos = \is_string($pe = $request->get('permisos')) ? $pe : null;
        $d->telefono = \is_string($t = $request->get('telefono')) ? $t : null;
        $d->estimator = null !== $request->get('estimator') ? (string) $request->get('estimator') : null;
        $d->bond = null !== $request->get('bond') ? (string) $request->get('bond') : null;
        $d->retainage = null !== $request->get('retainage') ? (string) $request->get('retainage') : null;
        $d->chat = null !== $request->get('chat') ? (string) $request->get('chat') : null;
        $wa = $request->get('widget_access');
        $d->widget_access = \is_string($wa) ? $wa : null;

        return $d;
    }
}
