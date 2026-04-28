<?php

namespace App\Exception\Admin;

/**
 * Lanzada cuando un DTO {@see \App\Dto\Admin\AdminHttpRequestDtoInterface} falla la validación en el resolver.
 *
 * @see \App\EventSubscriber\AdminDtoValidationFailedSubscriber
 */
final class AdminDtoValidationFailedException extends \RuntimeException
{
    /**
     * @param array{success: false, error: string, violations: array<string, list<string>>} $payload
     */
    public function __construct(
        private readonly array $payload,
    ) {
        parent::__construct('Admin DTO validation failed');
    }

    /**
     * @return array{success: false, error: string, violations: array<string, list<string>>}
     */
    public function getPayload(): array
    {
        return $this->payload;
    }
}
