<?php

namespace App\LiveGrid\Port;

use App\LiveGrid\Hook\HookDefinition;

interface HookRegistryInterface
{
    /**
     * @return list<HookDefinition> Sorted by priority descending
     */
    public function getHooks(string $hookName): array;
}
