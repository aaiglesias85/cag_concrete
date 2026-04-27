<?php

namespace App\Dto\Api\Response\Usuario;

final readonly class UsuarioSalvarImagenFailureResponse implements \JsonSerializable
{
    public function __construct(public string $error, public bool $success = false)
    {
    }

    public function jsonSerialize(): array
    {
        return [
            'success' => $this->success,
            'error' => $this->error,
        ];
    }
}
