<?php

namespace App\Dto\Api\Response\Project;

use App\Dto\Api\Response\Project\Payload\ProjectListRowPayload;

/**
 * Respuesta de GET /api/{lang}/project/listar.
 */
final readonly class ProjectListarResponse implements \JsonSerializable
{
    /**
     * @param list<ProjectListRowPayload>|null $projects
     */
    public function __construct(
        public bool $success,
        public ?int $total = null,
        /** @var list<ProjectListRowPayload>|null */
        public ?array $projects = null,
        public ?string $error = null,
    ) {
    }

    /**
     * @param array<string, mixed> $r Resultado de {@see \App\Service\App\ProjectService::ListarProjects}
     */
    public static function fromServiceResult(array $r): self
    {
        $wrapped = null;
        if (isset($r['projects']) && \is_array($r['projects'])) {
            $wrapped = [];
            foreach ($r['projects'] as $row) {
                if (\is_array($row)) {
                    $wrapped[] = ProjectListRowPayload::fromArray($row);
                }
            }
        }

        return new self(
            (bool) ($r['success'] ?? false),
            isset($r['total']) ? (int) $r['total'] : null,
            $wrapped,
            isset($r['error']) ? (string) $r['error'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        $o = ['success' => $this->success];
        if (null !== $this->total) {
            $o['total'] = $this->total;
        }
        if (null !== $this->projects) {
            $o['projects'] = array_map(
                static fn (ProjectListRowPayload $p) => $p->jsonSerialize(),
                $this->projects
            );
        }
        if (null !== $this->error) {
            $o['error'] = $this->error;
        }

        return $o;
    }
}
