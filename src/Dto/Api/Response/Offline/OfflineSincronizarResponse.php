<?php

namespace App\Dto\Api\Response\Offline;

/**
 * Respuesta de POST /api/{lang}/offline/sincronizar (después de fusionar mensaje traducido en el controlador).
 *
 * @phpstan-type UsuarioPayload array<string, mixed>
 */
final readonly class OfflineSincronizarResponse implements \JsonSerializable
{
    /**
     * @param UsuarioPayload|null $usuario
     */
    public function __construct(
        public bool $success,
        public ?string $message = null,
        public ?array $usuario = null,
        public ?string $error = null,
    ) {
    }

    /**
     * @param array<string, mixed> $r Payload ya preparado (service + message opcional del controlador)
     */
    public static function fromPayload(array $r): self
    {
        return new self(
            (bool) ($r['success'] ?? false),
            isset($r['message']) ? (string) $r['message'] : null,
            isset($r['usuario']) && \is_array($r['usuario']) ? $r['usuario'] : null,
            isset($r['error']) ? (string) $r['error'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        $o = ['success' => $this->success];
        if (null !== $this->message) {
            $o['message'] = $this->message;
        }
        if (null !== $this->usuario) {
            $o['usuario'] = $this->usuario;
        }
        if (null !== $this->error) {
            $o['error'] = $this->error;
        }

        return $o;
    }
}
