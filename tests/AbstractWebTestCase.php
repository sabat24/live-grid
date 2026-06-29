<?php

namespace App\Tests;

use App\Component\User\Entity\User;
use App\Component\User\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractWebTestCase extends WebTestCase
{
    protected function loginAsAdmin(): KernelBrowser
    {
        $client = static::createClient();
        $admin = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'admin@live-grid.com']);
        self::assertNotNull($admin, 'Admin user fixture must be loaded (admin@live-grid.com).');

        $client->loginUser($admin);

        return $client;
    }

    protected function getAdminUser(): User
    {
        static::createClient();
        $admin = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'admin@live-grid.com']);
        self::assertNotNull($admin);

        return $admin;
    }

    protected function requestUsersIndex(KernelBrowser $client): KernelBrowser
    {
        $client->request('GET', '/admin/users/');

        if ($client->getResponse()->isRedirect()) {
            $client->followRedirect();
        }

        self::assertResponseIsSuccessful();

        return $client;
    }
}
