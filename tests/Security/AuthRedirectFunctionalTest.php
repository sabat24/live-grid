<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Tests\AbstractWebTestCase;

final class AuthRedirectFunctionalTest extends AbstractWebTestCase
{
    public function testGuestHomeRedirectsToLogin(): void
    {
        $client = AuthRedirectFunctionalTest::createClient();
        $client->request('GET', '/');

        self::assertResponseRedirects('/login');
    }

    public function testGuestLoginEntryRedirectsToDefaultLocaleLogin(): void
    {
        $client = AuthRedirectFunctionalTest::createClient();
        $client->request('GET', '/login');

        self::assertResponseRedirects('/en/login');
    }

    public function testGuestLocalizedLoginRendersForm(): void
    {
        $client = AuthRedirectFunctionalTest::createClient();
        $client->request('GET', '/en/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form input[name="email"]');
    }

    public function testAdminHomeRedirectsToDashboard(): void
    {
        $client = $this->loginAsAdmin();
        $client->request('GET', '/');

        self::assertResponseRedirects('/admin');
    }

    public function testAdminLoginEntryRedirectsToDashboard(): void
    {
        $client = $this->loginAsAdmin();
        $client->request('GET', '/login');

        self::assertResponseRedirects('/admin');
    }

    public function testAdminLocalizedLoginRedirectsToDashboard(): void
    {
        $client = $this->loginAsAdmin();
        $client->request('GET', '/en/login');

        self::assertResponseRedirects('/admin');
    }

    public function testGuestAdminAreaRedirectsToDefaultLocaleLogin(): void
    {
        $client = AuthRedirectFunctionalTest::createClient();
        $client->request('GET', '/admin/users/');

        self::assertResponseRedirects('/login');
        $client->followRedirect();
        self::assertResponseRedirects('/en/login');
    }
}
