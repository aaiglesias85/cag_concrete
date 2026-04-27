<?php

namespace App\Controller\Admin\Traits;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

trait AdminValidationResponseTrait
{
    /**
     * Panel admin: respuestas JSON 400 con el mismo shape que la API; mensajes con locale `en` para validación.
     *
     * @return array{success: false, error: string, violations: array<string, list<string>>}
     */
    protected function formatAdminValidationFailure(ConstraintViolationListInterface $violations): array
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

    /**
     * Fuerza locale de traducción `en` durante la validación (textos de constraint por defecto en inglés en el panel).
     */
    protected function validateAdminDto(ValidatorInterface $validator, object $dto, ?TranslatorInterface $translator = null): ConstraintViolationListInterface
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
