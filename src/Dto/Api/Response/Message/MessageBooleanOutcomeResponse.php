<?php

namespace App\Dto\Api\Response\Message;

/**
 * Respuestas de mutación con solo éxito/error: marcar leídos, eliminar mensaje, ocultar conversación.
 */
final readonly class MessageBooleanOutcomeResponse implements \JsonSerializable
{
    public function __construct(
        public bool $success,
        public ?string $error = null,
    ) {
    }

    /** @param array<string, mixed> $r */
    public static function fromServiceResult(array $r): self
    {
        return new self(
            (bool) ($r['success'] ?? false),
            isset($r['error']) ? (string) $r['error'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        $o = ['success' => $this->success];
        if (null !== $this->error) {
            $o['error'] = $this->error;
        }

        return $o;
    }
}
