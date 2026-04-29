<?php

namespace App\Dto\Api\Response\Message;

/**
 * Respuesta de GET /api/{lang}/message/conversaciones.
 */
final readonly class MessageListarConversacionesResponse implements \JsonSerializable
{
    /**
     * @param list<array<string, mixed>>|null $conversations
     */
    public function __construct(
        public bool $success,
        public ?array $conversations = null,
        public ?string $error = null,
    ) {
    }

    /** @param array<string, mixed> $r */
    public static function fromServiceResult(array $r): self
    {
        return new self(
            (bool) ($r['success'] ?? false),
            isset($r['conversations']) && \is_array($r['conversations']) ? $r['conversations'] : null,
            isset($r['error']) ? (string) $r['error'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        $o = ['success' => $this->success];
        if (null !== $this->conversations) {
            $o['conversations'] = $this->conversations;
        }
        if (null !== $this->error) {
            $o['error'] = $this->error;
        }

        return $o;
    }
}
