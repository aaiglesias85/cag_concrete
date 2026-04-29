<?php

namespace App\Dto\Api\Response\Message;

/**
 * Respuesta de GET /api/{lang}/message/usuarios.
 */
final readonly class MessageListarUsuariosResponse implements \JsonSerializable
{
    /**
     * @param list<array<string, mixed>>|null $users
     */
    public function __construct(
        public bool $success,
        public ?array $users = null,
        public ?string $error = null,
    ) {
    }

    /** @param array<string, mixed> $r */
    public static function fromServiceResult(array $r): self
    {
        return new self(
            (bool) ($r['success'] ?? false),
            isset($r['users']) && \is_array($r['users']) ? $r['users'] : null,
            isset($r['error']) ? (string) $r['error'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        $o = ['success' => $this->success];
        if (null !== $this->users) {
            $o['users'] = $this->users;
        }
        if (null !== $this->error) {
            $o['error'] = $this->error;
        }

        return $o;
    }
}
