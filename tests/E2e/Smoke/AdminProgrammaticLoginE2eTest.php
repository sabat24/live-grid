<?php

declare(strict_types=1);

namespace App\Tests\E2e\Smoke;

use App\Tests\E2e\AbstractAdminAuthenticatedE2eTestCase;

final class AdminProgrammaticLoginE2eTest extends AbstractAdminAuthenticatedE2eTestCase
{
    public function testProgrammaticLoginReachesAdminUsers(): void
    {
        $this->visit('/admin/users');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('#signin-form');
    }
}
