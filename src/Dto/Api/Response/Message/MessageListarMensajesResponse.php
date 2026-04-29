<?php

namespace App\Dto\Api\Response\Message;

/**
 * Respuesta de GET /api/{lang}/message/mensajes.
 */
final readonly class MessageListarMensajesResponse implements \JsonSerializable
{
    /**
     * @param list<array<string, mixed>>|null $messages
     * @param array<string, mixed>|null       $other_user
     */
    public function __construct(
        public bool $success,
        public ?array $messages = null,
        public ?array $other_user = null,
        public ?string $error = null,
    ) {
    }

    /** @param array<string, mixed> $r */
    public static function fromServiceResult(array $r): self
    {
        return new self(
            (bool) ($r['success'] ?? false),
            isset($r['messages']) && \is_array($r['messages']) ? $r['messages'] : null,
            isset($r['other_user']) && \is_array($r['other_user']) ? $r['other_user'] : null,
            isset($r['error']) ? (string) $r['error'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        $o = ['success' => $this->success];
        if (null !== $this->messages) {
            $o['messages'] = $this->messages;
        }
        if (null !== $this->other_user) {
            $o['other_user'] = $this->other_user;
        }
        if (null !== $this->error) {
            $o['error'] = $this->error;
        }

        return $o;
    }
}
