<?php

namespace App\Dto\Api\Response\Usuario;

/**
 * @phpstan-param array<string, mixed> $usuario
 */
final readonly class UsuarioCargarDatosSuccessResponse implements \JsonSerializable
{
    /**
     * @param array<string, mixed> $usuario
     */
    public function __construct(
        public array $usuario,
        public bool $success = true,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'success' => $this->success,
            'usuario' => $this->usuario,
        ];
    }
}
