<?php

namespace App\Dto\Api\Response\Message;

/**
 * Respuesta de POST /api/{lang}/message/enviar.
 */
final readonly class MessageEnviarMensajeResponse implements \JsonSerializable
{
    /**
     * @param array<string, mixed>|null $message
     */
    public function __construct(
        public bool $success,
        public ?array $message = null,
        public ?string $error = null,
    ) {
    }

    /** @param array<string, mixed> $r */
    public static function fromServiceResult(array $r): self
    {
        return new self(
            (bool) ($r['success'] ?? false),
            isset($r['message']) && \is_array($r['message']) ? $r['message'] : null,
            isset($r['error']) ? (string) $r['error'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        if ($this->success) {
            return [
                'success' => true,
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
