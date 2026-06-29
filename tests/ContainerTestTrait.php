<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\TestContainer;

trait ContainerTestTrait
{
    protected function getTestContainer(): TestContainer
    {
        if (static::$kernel === null) {
            self::bootKernel();
        }

        return static::getContainer()->get('test.service_container');
    }
}
