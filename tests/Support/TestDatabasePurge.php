<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Truncates all mapped entity tables (schema preserved). Uses ORMPurger truncate mode;
 * on MySQL temporarily disables foreign key checks so TRUNCATE succeeds on FK graphs.
 */
final class TestDatabasePurge
{
    public static function purge(EntityManagerInterface $entityManager): void
    {
        $connection = $entityManager->getConnection();
        $mysql = $connection->getDatabasePlatform() instanceof AbstractMySQLPlatform;

        if ($mysql) {
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        }

        try {
            $purger = new ORMPurger($entityManager);
            $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
            $purger->purge();
        } finally {
            if ($mysql) {
                $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
            }
        }

        $entityManager->clear();
    }
}
