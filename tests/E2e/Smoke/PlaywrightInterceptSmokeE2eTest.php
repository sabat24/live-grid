<?php

declare(strict_types=1);

namespace App\Tests\E2e\Smoke;

use App\Tests\E2e\AbstractE2eTestCase;

final class PlaywrightInterceptSmokeE2eTest extends AbstractE2eTestCase
{
    public function testLoginPageIsInterceptedByKernel(): void
    {
        $this->visit('/en_GB/login');
        $this->assertResponseIsSuccessful();
    }
}
