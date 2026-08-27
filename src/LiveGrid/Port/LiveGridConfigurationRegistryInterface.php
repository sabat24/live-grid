<?php

namespace App\LiveGrid\Port;

use App\LiveGrid\Model\LiveGridConfiguration;

interface LiveGridConfigurationRegistryInterface
{
    public function get(string $name): LiveGridConfiguration;

    public function has(string $name): bool;

    /**
     * @return list<string>
     */
    public function names(): array;
}
