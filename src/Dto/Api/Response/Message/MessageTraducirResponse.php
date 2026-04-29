<?php

namespace App\Dto\Api\Response\Message;

/**
 * Respuesta de POST /api/{lang}/message/traducir.
 */
final readonly class MessageTraducirResponse implements \JsonSerializable
{
    public function __construct(
        public bool $success,
        public ?string $translated_text = null,
        public ?string $error = null,
    ) {
    }

    /** @param array<string, mixed> $r */
    public static function fromServiceResult(array $r): self
    {
        return new self(
            (bool) ($r['success'] ?? false),
            isset($r['translated_text']) ? (string) $r['translated_text'] : null,
            isset($r['error']) ? (string) $r['error'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        $o = ['success' => $this->success];
        if (null !== $this->translated_text) {
            $o['translated_text'] = $this->translated_text;
        }
        if (null !== $this->error) {
            $o['error'] = $this->error;
        }

        return $o;
    }
}
