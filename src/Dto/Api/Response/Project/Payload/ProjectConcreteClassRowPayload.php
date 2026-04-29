<?php

namespace App\Dto\Api\Response\Project\Payload;

/**
 * Fila de concrete_classes[] en el detalle de proyecto (API app).
 * Origen: {@see \App\Service\Admin\ProjectService::ListarConcreteClassesDeProject}.
 */
final readonly class ProjectConcreteClassRowPayload implements \JsonSerializable
{
    public function __construct(
        public mixed $id,
        public mixed $concrete_class_id,
        public string $concrete_class_name,
        public mixed $concrete_quote_price,
        public int $posicion,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            $row['id'] ?? null,
            $row['concrete_class_id'] ?? '',
            (string) ($row['concrete_class_name'] ?? ''),
            $row['concrete_quote_price'] ?? null,
            isset($row['posicion']) ? (int) $row['posicion'] : 0,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'concrete_class_id' => $this->concrete_class_id,
            'concrete_class_name' => $this->concrete_class_name,
            'concrete_quote_price' => $this->concrete_quote_price,
            'posicion' => $this->posicion,
        ];
    }
}
