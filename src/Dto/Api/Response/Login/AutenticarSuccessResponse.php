<?php

namespace App\Dto\Api\Response\Login;

/**
 * 200 — login correcto (JWT + usuario en base64).
 *
 * @phpstan-type ServiceSuccess array{success: true, access_token: string, expires: int, usuario: string}
 */
final readonly class AutenticarSuccessResponse implements \JsonSerializable
{
    public function __construct(
        public string $access_token,
        public int $expires,
        public string $usuario,
        public bool $success = true,
    ) {
    }

    /**
     * @param ServiceSuccess|array<string, mixed> $r
     */
    public static function fromServiceResult(array $r): self
    {
        return new self(
            $r['access_token'],
            (int) $r['expires'],
            $r['usuario'],
            (bool) $r['success'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'success' => $this->success,
            'access_token' => $this->access_token,
            'expires' => $this->expires,
            'usuario' => $this->usuario,
        ];
    }
}
