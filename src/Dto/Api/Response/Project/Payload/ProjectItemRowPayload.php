<?php

namespace App\Dto\Api\Response\Project\Payload;

/**
 * Fila de items[] en el detalle de proyecto (API app).
 * Base: {@see \App\Service\Admin\ProjectService::DevolverItemDeProject}.
 * La app añade opcionalmente item_history por ítem ({@see \App\Service\App\ProjectService::CargarDatosProject}).
 *
 * El resto de claves del ítem se conservan tal cual en el wire format.
 *
 * @phpstan-type ProjectItemWire array<string, mixed>
 */
final readonly class ProjectItemRowPayload implements \JsonSerializable
{
    /** @var ProjectItemWire */
    private array $wire;

    /**
     * @param ProjectItemWire $row
     */
    private function __construct(array $row)
    {
        $this->wire = $row;
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        if (isset($row['item_history']) && \is_array($row['item_history'])) {
            $mapped = [];
            foreach ($row['item_history'] as $h) {
                if (\is_array($h)) {
                    $mapped[] = ProjectItemHistoryRowPayload::fromArray($h)->jsonSerialize();
                }
            }
            $row['item_history'] = $mapped;
        }

        return new self($row);
    }

    /**
     * @return ProjectItemWire
     */
    public function jsonSerialize(): array
    {
        return $this->wire;
    }

    /**
     * @return ProjectItemWire
     */
    public function toArray(): array
    {
        return $this->wire;
    }

    /**
     * @return list<ProjectItemHistoryRowPayload>
     */
    public function itemHistoryTyped(): array
    {
        $rows = $this->wire['item_history'] ?? [];
        if (!\is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $h) {
            if (\is_array($h)) {
                $out[] = ProjectItemHistoryRowPayload::fromArray($h);
            }
        }

        return $out;
    }
}
