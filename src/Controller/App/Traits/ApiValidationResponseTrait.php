<?php

namespace App\Controller\App\Traits;

use Symfony\Component\Validator\ConstraintViolationListInterface;

trait ApiValidationResponseTrait
{
    /**
     * @return array{success: false, error: string, violations: array<string, list<string>>}
     */
    protected function formatValidationFailure(ConstraintViolationListInterface $violations): array
    {
        $byField = [];
        foreach ($violations as $violation) {
            $path = '' !== $violation->getPropertyPath() ? $violation->getPropertyPath() : '_global';
            $byField[$path][] = $violation->getMessage();
        }

        $firstMessage = $violations->count() > 0 ? $violations[0]->getMessage() : 'Validation failed';

        return [
            'success' => false,
            'error' => $firstMessage,
            'violations' => $byField,
        ];
    }
}
