<?php

declare(strict_types=1);

namespace App\Tests\E2e;

use App\Tests\E2e\Support\AdminLoginTrait;

abstract class AbstractAdminAuthenticatedE2eTestCase extends AbstractE2eTestCase
{
    use AdminLoginTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAsE2eAdmin();
    }
}
