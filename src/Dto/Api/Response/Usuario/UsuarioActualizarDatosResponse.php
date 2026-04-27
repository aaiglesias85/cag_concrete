<?php

namespace App\Dto\Api\Response\Usuario;

/** Éxito o error al actualizar perfil */
final readonly class UsuarioActualizarDatosResponse implements \JsonSerializable
{
    public function __construct(
        public bool $success,
        public ?string $message = null,
        public ?string $error = null,
    ) {
    }

    public function jsonSerialize(): array
    {
        if ($this->success) {
            return [
                'success' => true,
                'message' => (string) $this->message,
            ];
        }

        return [
            'success' => false,
            'error' => (string) $this->error,
        ];
    }
}
