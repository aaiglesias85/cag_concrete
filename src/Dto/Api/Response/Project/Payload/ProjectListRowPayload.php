<?php

namespace App\Dto\Api\Response\Project\Payload;

/**
 * Una fila del listado GET /api/{lang}/project/listar (normalización en {@see \App\Service\App\ProjectService::ListarProjects}).
 *
 * @phpstan-type ProjectListRowWire array<string, mixed>
 */
final readonly class ProjectListRowPayload implements \JsonSerializable
{
    /**
     * @param ProjectListRowWire $row
     */
    public function __construct(private array $row)
    {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self($row);
    }

    /**
     * @return ProjectListRowWire
     */
    public function jsonSerialize(): array
    {
        return $this->row;
    }

    /**
     * @return ProjectListRowWire
     */
    public function toArray(): array
    {
        return $this->row;
    }
}
