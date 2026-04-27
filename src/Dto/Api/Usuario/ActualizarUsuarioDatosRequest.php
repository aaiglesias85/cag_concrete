<?php

namespace App\Dto\Api\Usuario;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Body JSON de POST /api/usuario/actualizarDatos (campos opcionales; se fusionan con el usuario actual en el controlador).
 */
final class ActualizarUsuarioDatosRequest
{
    #[Assert\Length(max: 255)]
    public ?string $nombre = null;

    #[Assert\Length(max: 255)]
    public ?string $apellidos = null;

    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public ?string $email = null;

    #[Assert\Length(max: 64)]
    public ?string $telefono = null;

    public ?string $password_actual = null;

    public ?string $password = null;

    #[Assert\Choice(choices: ['es', 'en'])]
    public ?string $preferred_lang = null;

    #[Assert\Callback]
    public function validatePasswordPair(ExecutionContextInterface $context): void
    {
        $new = $this->password ?? '';
        $old = $this->password_actual ?? '';
        if ('' !== $new && '' === trim((string) $old)) {
            $context->buildViolation('api.validation.password_change_requires_current')
                ->setTranslationDomain('validators')
                ->atPath('password_actual')
                ->addViolation();
        }
    }
}
