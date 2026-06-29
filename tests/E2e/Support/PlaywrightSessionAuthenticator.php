<?php

declare(strict_types=1);

namespace App\Tests\E2e\Support;

use App\Component\User\Entity\User;
use App\Tests\E2e\Fixture\E2eCoreFixtureLoader;
use Doctrine\ORM\EntityManagerInterface;
use Playwright\Symfony\Client\PlaywrightKernelClient;
use Symfony\Bundle\FrameworkBundle\Test\TestBrowserToken;
use Symfony\Bundle\FrameworkBundle\Test\TestContainer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Programmatic login for Playwright E2E — mirrors KernelBrowser::loginUser() and syncs the session cookie to the browser.
 */
final class PlaywrightSessionAuthenticator
{
    private static ?string $cachedSessionName = null;

    private static ?string $cachedSessionId = null;

    public static function login(
        PlaywrightKernelClient $client,
        ContainerInterface $container,
        UserInterface $user,
        string $firewallContext = 'main',
    ): void {
        if (null === self::$cachedSessionId || null === self::$cachedSessionName) {
            $container = self::testContainer($container);

            $token = new TestBrowserToken($user->getRoles(), $user, $firewallContext);

            $session = $container->get('session.factory')->createSession();
            $session->start();
            $session->set('_security_'.$firewallContext, serialize($token));
            $session->save();

            self::$cachedSessionName = $session->getName();
            self::$cachedSessionId = $session->getId();
        }

        $client->setCookie(self::$cachedSessionName, self::$cachedSessionId);
    }

    public static function resetCachedSession(): void
    {
        self::$cachedSessionName = null;
        self::$cachedSessionId = null;
    }

    public static function loadE2eAdminUser(ContainerInterface $container): User
    {
        $container = self::testContainer($container);

        $user = $container
            ->get(EntityManagerInterface::class)
            ->getRepository(User::class)
            ->findOneBy(['email' => E2eCoreFixtureLoader::ADMIN_EMAIL]);

        if (!$user instanceof User) {
            throw new \RuntimeException('E2E admin user not found. Were core fixtures loaded?');
        }

        return $user;
    }

    private static function testContainer(ContainerInterface $container): TestContainer
    {
        return $container->get('test.service_container');
    }
}
