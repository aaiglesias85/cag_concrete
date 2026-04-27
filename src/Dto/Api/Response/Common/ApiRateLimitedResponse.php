<?php

namespace App\Dto\Api\Response\Common;

/** 429 — mismo shape que otros errores simples */
final readonly class ApiRateLimitedResponse implements \JsonSerializable
{
    public function __construct(public string $error)
    {
    }

    public function jsonSerialize(): array
    {
        return [
            'success' => false,
            'error' => $this->error,
        ];
    }
}
