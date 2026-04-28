<?php

declare(strict_types=1);

namespace App\Tests\Admin;

use App\Dto\Admin\Advertisement\AdvertisementIdRequest;
use App\Service\Admin\AdminDtoValidationService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AdminDtoValidationServiceTest extends KernelTestCase
{
    public function testFormatViolationPayloadMatchesExpectedShape(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $service = $container->get(AdminDtoValidationService::class);

        $dto = new AdvertisementIdRequest();
        $violations = $service->validate($dto);

        self::assertGreaterThan(0, \count($violations));

        $payload = $service->formatFailure($violations);
        self::assertFalse($payload['success']);
        self::assertIsString($payload['error']);
        self::assertIsArray($payload['violations']);
        self::assertArrayHasKey('advertisement_id', $payload['violations']);
    }

    public function testValidateRestoresTranslatorLocaleWhenLocaleAware(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $translator = $container->get(TranslatorInterface::class);

        if (!$translator instanceof LocaleAwareInterface) {
            self::markTestSkipped('Translator is not locale-aware in this environment.');
        }

        $previous = $translator->getLocale();
        $translator->setLocale('es');

        $service = $container->get(AdminDtoValidationService::class);
        $dto = new AdvertisementIdRequest();
        $service->validate($dto);

        self::assertSame('es', $translator->getLocale(), 'Locale must be restored after validate().');
        $translator->setLocale($previous);
    }
}
