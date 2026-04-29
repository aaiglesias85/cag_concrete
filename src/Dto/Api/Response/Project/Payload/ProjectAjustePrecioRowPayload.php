<?php

namespace App\Dto\Api\Response\Project\Payload;

/**
 * Fila de ajustes_precio[] en el detalle de proyecto (API app).
 * Origen: {@see \App\Service\Admin\ProjectService::ListarAjustesPrecioDeProject}.
 */
final readonly class ProjectAjustePrecioRowPayload implements \JsonSerializable
{
    public function __construct(
        public mixed $id,
        public string $day,
        public mixed $percent,
        public string $items_id,
        public string $items_names,
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
            (string) ($row['day'] ?? ''),
            $row['percent'] ?? null,
            (string) ($row['items_id'] ?? ''),
            (string) ($row['items_names'] ?? ''),
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
            'day' => $this->day,
            'percent' => $this->percent,
            'items_id' => $this->items_id,
            'items_names' => $this->items_names,
            'posicion' => $this->posicion,
        ];
    }
}
