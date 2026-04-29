<?php

namespace App\Dto\Api\Response\Project;

use App\Dto\Api\Response\Project\Payload\ProjectDetailPayload;

/**
 * Respuesta de GET /api/{lang}/project/cargarDatos.
 */
final readonly class ProjectCargarDatosResponse implements \JsonSerializable
{
    public function __construct(
        public bool $success,
        public ?ProjectDetailPayload $project = null,
        public ?string $error = null,
    ) {
    }

    /**
     * @param array<string, mixed> $r Resultado de {@see \App\Service\App\ProjectService::CargarDatosProject}
     */
    public static function fromServiceResult(array $r, string $fallbackError): self
    {
        if (!empty($r['success']) && isset($r['project']) && \is_array($r['project'])) {
            return new self(true, ProjectDetailPayload::fromArray($r['project']), null);
        }

        $err = isset($r['error']) && '' !== (string) $r['error']
            ? (string) $r['error']
            : $fallbackError;

        return new self(false, null, $err);
    }

    public function jsonSerialize(): array
    {
        if ($this->success && null !== $this->project) {
            return [
                'success' => true,
                'project' => $this->project->jsonSerialize(),
            ];
        }

        return [
            'success' => false,
            'error' => $this->error ?? '',
        ];
    }
}
