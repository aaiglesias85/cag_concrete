<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Rutas de prueba de correo: `access_control` con ROLE_ADMIN y redirección desde /test-email.
 * Matriz ROLE_USER vs ROLE_ADMIN: verificación manual o fixtures + usuario en BD de test.
 */
class TestEmailRouteTest extends WebTestCase
{
    public function testAnonymousAccessRedirectsToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/test-email');
        self::assertResponseRedirects();
    }

    public function testLegacyPathRedirectsToNewUrl(): void
    {
        $client = static::createClient();
        $client->request('GET', '/test-email');
        self::assertResponseRedirects();
        self::assertStringEndsWith('/admin/test-email', $client->getResponse()->headers->get('Location') ?? '');
        self::assertSame(301, $client->getResponse()->getStatusCode());
    }
}
