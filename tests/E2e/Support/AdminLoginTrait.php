<?php

declare(strict_types=1);

namespace App\Tests\E2e\Support;

use Playwright\Symfony\Test\PlaywrightTestCase;

/**
 * @mixin PlaywrightTestCase
 */
trait AdminLoginTrait
{
    protected function loginAsE2eAdmin(): void
    {
        $container = self::getContainer();
        PlaywrightSessionAuthenticator::login(
            $this->client,
            $container,
            PlaywrightSessionAuthenticator::loadE2eAdminUser($container),
        );
    }
}
