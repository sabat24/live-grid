<?php

declare(strict_types=1);

namespace App\Factory;

use App\Component\User\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    public static function class(): string
    {
        return User::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'email' => self::faker()->unique()->email(),
            'username' => self::faker()->unique()->userName(),
            'password' => 'password',
            'roles' => [],
        ];
    }

    protected function initialize(): static
    {
        return $this->afterInstantiate(function (User $user, array $attributes): void {
            if (!isset($attributes['password']) || !\is_string($attributes['password'])) {
                return;
            }

            $user->setPassword($this->passwordHasher->hashPassword($user, $attributes['password']));
        });
    }
}
