<?php

namespace App\Dto\Api\Response\Project\Payload;

/**
 * Fila de item_history dentro de un ítem del proyecto (API app).
 * Origen: {@see \App\Service\Admin\ProjectService::ListarHistorialDeItem}.
 */
final readonly class ProjectItemHistoryRowPayload implements \JsonSerializable
{
    public function __construct(
        public mixed $id,
        public mixed $action_type,
        public string $mensaje,
        public string $fecha,
        public string $user_name,
        public mixed $old_value,
        public mixed $new_value,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            $row['id'] ?? null,
            $row['action_type'] ?? '',
            (string) ($row['mensaje'] ?? ''),
            (string) ($row['fecha'] ?? ''),
            (string) ($row['user_name'] ?? ''),
            $row['old_value'] ?? null,
            $row['new_value'] ?? null,
        );
    }

    /**
     * @return array{
     *     id: mixed,
     *     action_type: mixed,
     *     mensaje: string,
     *     fecha: string,
     *     user_name: string,
     *     old_value: mixed,
     *     new_value: mixed
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'action_type' => $this->action_type,
            'mensaje' => $this->mensaje,
            'fecha' => $this->fecha,
            'user_name' => $this->user_name,
            'old_value' => $this->old_value,
            'new_value' => $this->new_value,
        ];
    }
}
