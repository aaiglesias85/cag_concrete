<?php

namespace App\Dto\Api\Response\Login;

/**
 * Credenciales incorrectas u otro fallo de autenticación (401).
 */
final readonly class AutenticarFailureResponse implements \JsonSerializable
{
    public function __construct(
        public string $error,
        public ?int $intento_login = null,
        public bool $success = false,
    ) {
    }

    /**
     * @param array{success: false, error: string, intento_login?: int|null} $r
     */
    public static function fromServiceResult(array $r): self
    {
        return new self(
            $r['error'],
            isset($r['intento_login']) ? (int) $r['intento_login'] : null,
            (bool) $r['success'],
        );
    }

    public function jsonSerialize(): array
    {
        $out = [
            'success' => $this->success,
            'error' => $this->error,
        ];
        if (null !== $this->intento_login) {
            $out['intento_login'] = $this->intento_login;
        }

        return $out;
    }
}
