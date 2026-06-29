<?php

namespace App\Component\User\Repository;

use App\Component\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\ResourceRepositoryTrait;
use Sylius\Component\Resource\Repository\RepositoryInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @implements RepositoryInterface<User>
 */
class UserRepository extends ServiceEntityRepository implements RepositoryInterface
{
    use ResourceRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
}
