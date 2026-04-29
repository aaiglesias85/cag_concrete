<?php

namespace App\Dto\Api\Response\Message;

/**
 * Respuesta de GET /api/{lang}/message/conversacion.
 *
 * En éxito, conversation_id puede ser null si aún no existe conversación con ese usuario.
 *
 * @phpstan-type OtherUser array<string, mixed>
 */
final readonly class MessageObtenerConversacionResponse implements \JsonSerializable
{
    /**
     * @param OtherUser|null $other_user
     */
    public function __construct(
        public bool $success,
        public ?int $conversation_id = null,
        public ?array $other_user = null,
        public ?string $error = null,
    ) {
    }

    /** @param array<string, mixed> $r */
    public static function fromServiceResult(array $r): self
    {
        $success = (bool) ($r['success'] ?? false);
        $cid = $r['conversation_id'] ?? null;

        return new self(
            $success,
            null !== $cid ? (int) $cid : null,
            isset($r['other_user']) && \is_array($r['other_user']) ? $r['other_user'] : null,
            isset($r['error']) ? (string) $r['error'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        if (!$this->success) {
            $o = ['success' => false];
            if (null !== $this->error) {
                $o['error'] = $this->error;
            }

            return $o;
        }

        return [
            'success' => true,
            'conversation_id' => $this->conversation_id,
            'other_user' => $this->other_user ?? [],
        ];
    }
}
