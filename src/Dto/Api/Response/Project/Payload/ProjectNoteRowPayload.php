<?php

namespace App\Dto\Api\Response\Project\Payload;

/**
 * Fila de notes[] en el detalle de proyecto (API app).
 * Origen: {@see \App\Service\Admin\ProjectService::ListarNotesDeProject}.
 */
final readonly class ProjectNoteRowPayload implements \JsonSerializable
{
    public function __construct(
        public mixed $id,
        public string $date,
        public string $notes,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            $row['id'] ?? null,
            (string) ($row['date'] ?? ''),
            (string) ($row['notes'] ?? ''),
        );
    }

    /**
     * @return array{id: mixed, date: string, notes: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'notes' => $this->notes,
        ];
    }
}
