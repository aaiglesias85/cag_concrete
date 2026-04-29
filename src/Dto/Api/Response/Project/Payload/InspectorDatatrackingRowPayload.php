<?php

namespace App\Dto\Api\Response\Project\Payload;

/**
 * Fila de inspectors_datatracking en el detalle de proyecto (API app).
 *
 * @phpstan-type InspectorDtWire array{
 *     inspector_id: mixed,
 *     name: mixed,
 *     email: mixed,
 *     phone: mixed,
 *     status: int|float,
 *     posicion: int
 * }
 */
final readonly class InspectorDatatrackingRowPayload implements \JsonSerializable
{
    public function __construct(
        public mixed $inspector_id,
        public mixed $name,
        public mixed $email,
        public mixed $phone,
        public int|float $status,
        public int $posicion,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            $row['inspector_id'] ?? null,
            $row['name'] ?? '',
            $row['email'] ?? '',
            $row['phone'] ?? '',
            isset($row['status']) ? (int) $row['status'] : 0,
            isset($row['posicion']) ? (int) $row['posicion'] : 0,
        );
    }

    /**
     * @return InspectorDtWire
     */
    public function jsonSerialize(): array
    {
        return [
            'inspector_id' => $this->inspector_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'posicion' => $this->posicion,
        ];
    }
}
