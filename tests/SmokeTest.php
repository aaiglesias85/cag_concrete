<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Pruebas mínimas de humo: kernel y una ruta pública sin lógica de negocio pesada.
 */
class SmokeTest extends WebTestCase
{
    public function testKernelBootsInTestEnvironment(): void
    {
        $kernel = self::bootKernel();
        self::assertSame('test', $kernel->getEnvironment());
    }

    public function testLoginPageIsReachable(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        self::assertResponseIsSuccessful();
    }
}
