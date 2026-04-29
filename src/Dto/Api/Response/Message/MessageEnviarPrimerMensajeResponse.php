<?php

namespace App\Dto\Api\Response\Message;

/**
 * Respuesta de POST /api/{lang}/message/enviar-primer-mensaje.
 */
final readonly class MessageEnviarPrimerMensajeResponse implements \JsonSerializable
{
    /**
     * @param array<string, mixed>|null $message
     */
    public function __construct(
        public bool $success,
        public ?int $conversation_id = null,
        public ?array $message = null,
        public ?string $error = null,
    ) {
    }

    /** @param array<string, mixed> $r */
    public static function fromServiceResult(array $r): self
    {
        $cid = $r['conversation_id'] ?? null;

        return new self(
            (bool) ($r['success'] ?? false),
            null !== $cid ? (int) $cid : null,
            isset($r['message']) && \is_array($r['message']) ? $r['message'] : null,
            isset($r['error']) ? (string) $r['error'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        if ($this->success) {
            return [
                'success' => true,
                'conversation_id' => $this->conversation_id,
                'message' => $this->message ?? [],
            ];
        }

        $o = ['success' => false];
        if (null !== $this->error) {
            $o['error'] = $this->error;
        }

        return $o;
    }
}
