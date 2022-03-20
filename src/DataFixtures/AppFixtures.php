<?php

namespace App\DataFixtures;

use App\Component\User\Entity\User;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('admin@live-grid.com');
        $user->setUsername('admin');
        $user->setPassword($this->passwordHasher->hashPassword($user, '111'));
        $user->setRoles(['ROLE_ADMIN']);
        $manager->persist($user);

        UserFactory::createMany(25);

        $manager->flush();
    }
}
