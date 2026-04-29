<?php

namespace App\Dto\Api\Response\Common;

/**
 * Respuesta JSON genérica para payloads arbitrarios (legacy).
 * En la API app (`App\Controller\App`) es preferible usar DTOs dedicados en `App\Dto\Api\Response\{Project,Offline,Message,...}`.
 *
 * @template-covariant T of array<string, mixed>
 */
final readonly class ApiJsonPayload implements \JsonSerializable
{
    /**
     * @param T $data
     */
    public function __construct(private array $data)
    {
    }

    /**
     * @return T
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
