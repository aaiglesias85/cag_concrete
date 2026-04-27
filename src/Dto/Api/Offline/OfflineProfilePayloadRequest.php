<?php

namespace App\Dto\Api\Offline;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Objeto profile_offline dentro de POST /api/{lang}/offline/sincronizar.
 */
final class OfflineProfilePayloadRequest
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

    public ?string $passwordactual = null;

    public ?string $password = null;

    public ?string $imagen = null;

    #[Assert\Callback]
    public function validatePasswordPair(ExecutionContextInterface $context): void
    {
        $new = $this->password ?? '';
        $old = $this->passwordactual ?? '';
        if ('' !== $new && '' === trim((string) $old)) {
            $context->buildViolation('api.validation.password_change_requires_current')
                ->setTranslationDomain('validators')
                ->atPath('passwordactual')
                ->addViolation();
        }
    }
}
