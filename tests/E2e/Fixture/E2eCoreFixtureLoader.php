<?php

declare(strict_types=1);

namespace App\Tests\E2e\Fixture;

use App\Component\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class E2eCoreFixtureLoader
{
    public const string ADMIN_EMAIL = 'admin@live-grid.com';

    public const string ADMIN_PASSWORD = '111';

    public static function load(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): void {
        if (self::adminExists($entityManager)) {
            return;
        }

        $admin = new User();
        $admin->setEmail(self::ADMIN_EMAIL);
        $admin->setUsername('admin');
        $admin->setPassword($passwordHasher->hashPassword($admin, self::ADMIN_PASSWORD));
        $admin->setRoles(['ROLE_ADMIN']);

        $entityManager->persist($admin);
        $entityManager->flush();
    }

    private static function adminExists(EntityManagerInterface $entityManager): bool
    {
        return null !== $entityManager->getRepository(User::class)->findOneBy(['email' => self::ADMIN_EMAIL]);
    }
}
