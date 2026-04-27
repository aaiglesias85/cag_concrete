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

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromDecodedArray(array $raw): self
    {
        $p = new self();
        $p->nombre = \array_key_exists('nombre', $raw) && \is_string($raw['nombre']) ? trim($raw['nombre']) : null;
        $p->apellidos = \array_key_exists('apellidos', $raw) && \is_string($raw['apellidos']) ? trim($raw['apellidos']) : null;
        $p->email = \array_key_exists('email', $raw) && \is_string($raw['email']) ? trim($raw['email']) : null;
        $p->telefono = \array_key_exists('telefono', $raw) && \is_string($raw['telefono']) ? trim($raw['telefono']) : null;
        $p->passwordactual = \array_key_exists('passwordactual', $raw) && \is_string($raw['passwordactual']) ? $raw['passwordactual'] : null;
        $p->password = \array_key_exists('password', $raw) && \is_string($raw['password']) ? $raw['password'] : null;
        $p->imagen = \array_key_exists('imagen', $raw) && \is_string($raw['imagen']) ? $raw['imagen'] : null;

        return $p;
    }
}
