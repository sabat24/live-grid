<?php

declare(strict_types=1);

namespace App\Tests\E2e;

use App\Tests\E2e\Client\LiveComponentAwareResponseConverter;
use App\Tests\E2e\Client\MultipartAwareRequestConverter;
use App\Tests\E2e\Fixture\E2eCoreFixtureLoader;
use App\Tests\Support\TestDatabasePurge;
use Doctrine\ORM\EntityManagerInterface;
use Playwright\Symfony\Client\Interception\AssetServer;
use Playwright\Symfony\Client\PlaywrightKernelClient;
use Playwright\Symfony\Test\PlaywrightTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Test\Factories;

abstract class AbstractE2eTestCase extends PlaywrightTestCase
{
    use Factories;

    private static bool $coreFixturesLoaded = false;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::$kernel;
        if (!$kernel instanceof KernelInterface) {
            self::fail('Kernel was not booted before Playwright client setup.');
        }

        $assetServer = self::getContainer()->get(AssetServer::class);

        $this->client = new PlaywrightKernelClient(
            $this->browser,
            $kernel,
            new MultipartAwareRequestConverter(),
            new LiveComponentAwareResponseConverter(),
            [],
            ['localhost', '127.0.0.1'],
            $this,
            $assetServer,
            $this->getBaseUrl(),
            $this->playwrightLogger,
            $this->debugLogging,
        );

        if (!self::$coreFixturesLoaded) {
            $container = self::getContainer();
            $entityManager = $container->get(EntityManagerInterface::class);
            TestDatabasePurge::purge($entityManager);
            E2eCoreFixtureLoader::load(
                $entityManager,
                $container->get(UserPasswordHasherInterface::class),
            );

            self::$coreFixturesLoaded = true;
        }

        $this->loadClassFixtures();
    }

    protected function loadClassFixtures(): void
    {
    }
}
