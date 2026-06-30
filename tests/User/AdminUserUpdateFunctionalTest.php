<?php

namespace App\Tests\User;

use App\Component\User\Repository\UserRepository;
use App\Tests\AbstractWebTestCase;

final class AdminUserUpdateFunctionalTest extends AbstractWebTestCase
{
    public function testUserUpdatePageIsForbiddenForReadOnlyAdmin(): void
    {
        $client = $this->loginAsAdmin();
        $user = AdminUserUpdateFunctionalTest::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'admin@live-grid.com']);
        self::assertNotNull($user);

        $client->request('GET', sprintf('/admin/users/%d/edit', $user->getId()));

        self::assertResponseStatusCodeSame(403);
    }
}
