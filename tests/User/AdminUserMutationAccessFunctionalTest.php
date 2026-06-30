<?php

namespace App\Tests\User;

use App\Component\User\Entity\User;
use App\Component\User\Repository\UserRepository;
use App\Tests\AbstractWebTestCase;

final class AdminUserMutationAccessFunctionalTest extends AbstractWebTestCase
{
    public function testUsersListIsAccessibleForReadOnlyAdmin(): void
    {
        $client = $this->loginAsAdmin();

        $this->requestUsersIndex($client);
    }

    public function testCreatePageIsForbiddenForReadOnlyAdmin(): void
    {
        $client = $this->loginAsAdmin();

        $client->request('GET', '/admin/users/new');

        self::assertResponseStatusCodeSame(403);
    }

    public function testEditPageIsForbiddenForReadOnlyAdmin(): void
    {
        $client = $this->loginAsAdmin();
        $user = $this->getTargetUser();

        $client->request('GET', sprintf('/admin/users/%d/edit', $user->getId()));

        self::assertResponseStatusCodeSame(403);
    }

    public function testDeleteIsForbiddenForReadOnlyAdmin(): void
    {
        $client = $this->loginAsAdmin();
        $user = $this->getTargetUser();

        $client->request('DELETE', sprintf('/admin/users/%d/delete', $user->getId()));

        self::assertResponseStatusCodeSame(403);
    }

    public function testUsersListShowsDisabledActionsForReadOnlyAdmin(): void
    {
        $client = $this->loginAsAdmin();
        $this->requestUsersIndex($client);
        $crawler = $client->getCrawler();

        self::assertGreaterThan(0, $crawler->filter('span.button.h-button.is-disabled[aria-disabled="true"]')->count());
        self::assertGreaterThan(0, $crawler->filter('span.dropdown-item.is-disabled[aria-disabled="true"]')->count());
        self::assertCount(0, $crawler->filter('a.button.h-button[href="/admin/users/new"]'));
        self::assertCount(0, $crawler->filter('a.dropdown-item[href*="/edit"]'));
        self::assertCount(0, $crawler->filter('a.dropdown-item[href*="/delete"]'));
    }

    private function getTargetUser(): User
    {
        $users = AdminUserMutationAccessFunctionalTest::getContainer()->get(UserRepository::class)->findAll();
        foreach ($users as $user) {
            if ($user->getEmail() !== 'admin@live-grid.com') {
                return $user;
            }
        }

        self::fail('Expected at least one non-admin user fixture.');
    }
}
