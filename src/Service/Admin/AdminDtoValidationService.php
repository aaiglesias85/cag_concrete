<?php

namespace App\Service\Admin;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Validación del panel admin con locale `en` y formato JSON de errores homogéneo.
 * Usado por {@see \App\Http\Controller\AdminHttpRequestDtoValueResolver} al validar DTOs del panel admin.
 */
final class AdminDtoValidationService
{
    public function __construct(
        private ValidatorInterface $validator,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @return array{success: false, error: string, violations: array<string, list<string>>}
     */
    public function formatFailure(ConstraintViolationListInterface $violations): array
    {
        return self::formatViolationPayload($violations);
    }

    /**
     * @return array{success: false, error: string, violations: array<string, list<string>>}
     */
    public static function formatViolationPayload(ConstraintViolationListInterface $violations): array
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

    public function validate(object $dto): ConstraintViolationListInterface
    {
        return self::validateWithLocale($this->validator, $dto, $this->translator);
    }

    /**
     * Fuerza locale de traducción `en` durante la validación (constraints por defecto en inglés en el panel).
     */
    public static function validateWithLocale(ValidatorInterface $validator, object $dto, ?TranslatorInterface $translator = null): ConstraintViolationListInterface
    {
        $previous = null;
        if ($translator instanceof LocaleAwareInterface) {
            $previous = $translator->getLocale();
            $translator->setLocale('en');
        }
        $violations = $validator->validate($dto);
        if ($translator instanceof LocaleAwareInterface && null !== $previous) {
            $translator->setLocale($previous);
        }

        return $violations;
    }
}
