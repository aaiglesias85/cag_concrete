<?php

namespace App\Dto\Api\Response\Common;

/**
 * Respuesta JSON genérica para payloads ya definidos por el servicio (listados, sincronización, etc.).
 * No altera la forma del wire format; solo tipa el contrato en el controlador.
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
