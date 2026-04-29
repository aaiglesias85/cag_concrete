<?php

namespace App\Dto\Api\Response\Offline;

/**
 * Respuesta de GET /api/{lang}/offline/listarInformacionRequerida.
 *
 * @phpstan-type CompanyRow array<string, mixed>
 */
final readonly class OfflineListarInformacionResponse implements \JsonSerializable
{
    /**
     * @param list<CompanyRow>|null $companies
     */
    public function __construct(
        public bool $success,
        public ?array $companies = null,
        public ?string $error = null,
    ) {
    }

    /**
     * @param array<string, mixed> $r Resultado de {@see \App\Service\App\OfflineService::ListarInformacionRequerida}
     */
    public static function fromServiceResult(array $r): self
    {
        return new self(
            (bool) ($r['success'] ?? false),
            isset($r['companies']) && \is_array($r['companies']) ? $r['companies'] : null,
            isset($r['error']) ? (string) $r['error'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        $o = ['success' => $this->success];
        if (null !== $this->companies) {
            $o['companies'] = $this->companies;
        }
        if (null !== $this->error) {
            $o['error'] = $this->error;
        }

        return $o;
    }
}
