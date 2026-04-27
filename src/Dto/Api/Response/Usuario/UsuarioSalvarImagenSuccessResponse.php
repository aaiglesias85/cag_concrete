<?php

namespace App\Dto\Api\Response\Usuario;

final readonly class UsuarioSalvarImagenSuccessResponse implements \JsonSerializable
{
    public function __construct(
        public string $imagen,
        public string $message,
        public bool $success = true,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'success' => $this->success,
            'imagen' => $this->imagen,
            'message' => $this->message,
        ];
    }
}
