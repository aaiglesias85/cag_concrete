<?php

namespace App\Dto\Api\Response\Project\Payload;

/**
 * Fila de archivos[] en el detalle de proyecto (API app).
 * Origen: {@see \App\Service\Admin\ProjectService::ListarArchivosDeProject}.
 */
final readonly class ProjectArchivoRowPayload implements \JsonSerializable
{
    public function __construct(
        public mixed $id,
        public string $name,
        public string $file,
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
            (string) ($row['name'] ?? ''),
            (string) ($row['file'] ?? ''),
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
            'name' => $this->name,
            'file' => $this->file,
            'posicion' => $this->posicion,
        ];
    }
}
