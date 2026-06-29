<?php

namespace App\Factory;

use App\Component\User\Entity\User;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<User>
 *
 * @method User|Proxy<User> create(array<string, mixed>|callable $attributes = [])
 * @method static User|Proxy<User> createOne(array<string, mixed> $attributes = [])
 * @method static User|Proxy<User> find(object|array<string, mixed> $criteria)
 * @method static User|Proxy<User> findOrCreate(array<string, mixed> $attributes)
 * @method static User|Proxy<User> first(string $sortedField = 'id')
 * @method static User|Proxy<User> last(string $sortedField = 'id')
 * @method static User|Proxy<User> random(array<string, mixed> $attributes = [])
 * @method static User|Proxy<User> randomOrCreate(array<string, mixed> $attributes = [])
 * @method static EntityRepository|RepositoryProxy<User> repository()
 * @method static list<User|Proxy<User>> all()
 * @method static list<User|Proxy<User>> createMany(int $number, array<string, mixed>|callable $attributes = [])
 * @method static list<User|Proxy<User>> createSequence(iterable<array<string, mixed>|callable> $sequence)
 * @method static list<User|Proxy<User>> findBy(array<string, mixed> $attributes)
 * @method static list<User|Proxy<User>> randomRange(int $min, int $max, array<string, mixed> $attributes = [])
 * @method static list<User|Proxy<User>> randomSet(int $number, array<string, mixed> $attributes = [])
 */
final class UserFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDefaults(): array
    {
        return [
            'email' => self::faker()->email(),
            'username' => self::faker()->userName(),
            'password' => self::faker()->text(),
            'roles' => [],
        ];
    }

    protected function initialize(): self
    {
        return $this;
    }

    protected static function getClass(): string
    {
        return User::class;
    }
}
