<?php

namespace App\Dto\Api\Response\Common;

/** error genérico — muchos endpoints móviles usan { success: false, error: string } */
final readonly class ApiSimpleFailureResponse implements \JsonSerializable
{
    public function __construct(
        public string $error,
        public bool $success = false,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'success' => $this->success,
            'error' => $this->error,
        ];
    }
}
