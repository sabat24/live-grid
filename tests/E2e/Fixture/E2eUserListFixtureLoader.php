<?php

declare(strict_types=1);

namespace App\Tests\E2e\Fixture;

use App\Component\User\Entity\User;
use App\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;

final class E2eUserListFixtureLoader
{
    private const int USER_COUNT = 25;

    public static function load(EntityManagerInterface $entityManager): void
    {
        $repository = $entityManager->getRepository(User::class);
        $existingCount = (int) $repository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        if ($existingCount >= self::USER_COUNT + 1) {
            return;
        }

        UserFactory::createMany(self::USER_COUNT);
        $entityManager->flush();
    }
}
