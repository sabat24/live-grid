<?php

declare(strict_types=1);

namespace App\Tests\E2e;

use App\Tests\E2e\Fixture\E2eUserListFixtureLoader;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractUserListE2eTestCase extends AbstractAdminAuthenticatedE2eTestCase
{
    private static bool $userListFixturesLoaded = false;

    protected function loadClassFixtures(): void
    {
        if (self::$userListFixturesLoaded) {
            return;
        }

        E2eUserListFixtureLoader::load(self::getContainer()->get(EntityManagerInterface::class));

        self::$userListFixturesLoaded = true;
    }
}
