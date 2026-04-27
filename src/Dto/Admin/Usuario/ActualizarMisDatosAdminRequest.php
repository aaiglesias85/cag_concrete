<?php

namespace App\Dto\Admin\Usuario;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/** POST user profile update (admin Usuario::actualizarMisDatos) */
final class ActualizarMisDatosAdminRequest
{
    #[Assert\NotBlank(message: 'User id is required.')]
    #[Assert\Positive]
    public ?int $usuario_id = null;

    public ?string $contrasenna_actual = null;
    public ?string $contrasenna = null;

    #[Assert\NotBlank(message: 'First name is required.')]
    public ?string $nombre = null;

    #[Assert\NotBlank(message: 'Last name is required.')]
    public ?string $apellidos = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    public ?string $telefono = null;

    #[Assert\Callback]
    public function passwordPair(ExecutionContextInterface $context): void
    {
        $new = $this->contrasenna ?? '';
        $old = $this->contrasenna_actual ?? '';
        if ('' !== $new && '' === (string) $old) {
            $context->buildViolation('Current password is required when setting a new password.')
                ->disableTranslation()
                ->atPath('contrasenna_actual')
                ->addViolation();
        }
    }

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $d->usuario_id = self::positiveIntOrNull($request->get('usuario_id'));
        $d->contrasenna_actual = \is_string($x = $request->get('password_actual')) ? $x : null;
        $d->contrasenna = \is_string($x = $request->get('password')) ? $x : null;
        $d->nombre = \is_string($x = $request->get('nombre')) ? $x : null;
        $d->apellidos = \is_string($x = $request->get('apellidos')) ? $x : null;
        $d->email = \is_string($x = $request->get('email')) ? $x : null;
        $d->telefono = \is_string($x = $request->get('telefono')) ? $x : null;

        return $d;
    }

    /**
     * @internal
     */
    private static function positiveIntOrNull(mixed $v): ?int
    {
        if (null === $v || false === $v || '' === $v) {
            return null;
        }
        if (\is_int($v)) {
            return $v > 0 ? $v : null;
        }
        if (\is_string($v) && is_numeric($v)) {
            $i = (int) $v;

            return $i > 0 ? $i : null;
        }

        return null;
    }
}
