<?php

namespace App\Dto\Api\Usuario;

use App\Dto\Api\JsonRequestBody;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @throws \Exception
     */
    public static function fromHttpRequest(Request $request): self
    {
        $data = JsonRequestBody::decodeAssociative($request);
        $dto = new self();
        $dto->nombre = \array_key_exists('nombre', $data) && \is_string($data['nombre']) ? trim($data['nombre']) : null;
        $dto->apellidos = \array_key_exists('apellidos', $data) && \is_string($data['apellidos']) ? trim($data['apellidos']) : null;
        $dto->email = \array_key_exists('email', $data) && \is_string($data['email']) ? trim($data['email']) : null;
        $dto->telefono = \array_key_exists('telefono', $data) && \is_string($data['telefono']) ? trim($data['telefono']) : null;
        $dto->password_actual = \array_key_exists('password_actual', $data) && \is_string($data['password_actual']) ? $data['password_actual'] : null;
        $dto->password = \array_key_exists('password', $data) && \is_string($data['password']) ? $data['password'] : null;
        if (\array_key_exists('preferred_lang', $data) && \is_string($data['preferred_lang'])) {
            $dto->preferred_lang = 'en' === $data['preferred_lang'] ? 'en' : ('es' === $data['preferred_lang'] ? 'es' : null);
        }

        return $dto;
    }
}
