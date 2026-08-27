<?php

namespace App\LiveGrid\Hook;

use App\LiveGrid\Port\HookRegistryInterface;

final class HookRegistry implements HookRegistryInterface
{
    /**
     * @param array<string, list<HookDefinition>> $hooks
     */
    public function __construct(
        private array $hooks = [],
    ) {
    }

    /**
     * @param array<string, array<string, array{template: string, priority?: int}>> $config
     */
    public function mergeConfig(array $config): void
    {
        foreach ($config as $hookName => $entries) {
            foreach ($entries as $id => $entry) {
                $this->add($hookName, new HookDefinition(
                    $id,
                    $entry['template'],
                    $entry['priority'] ?? 0,
                ));
            }
        }
    }

    public function add(string $hookName, HookDefinition $definition): void
    {
        $this->hooks[$hookName] ??= [];
        // Replace existing id under same hook
        $this->hooks[$hookName] = array_values(array_filter(
            $this->hooks[$hookName],
            static fn (HookDefinition $existing): bool => $existing->id !== $definition->id,
        ));
        $this->hooks[$hookName][] = $definition;
    }

    public function getHooks(string $hookName): array
    {
        $hooks = $this->hooks[$hookName] ?? [];
        usort(
            $hooks,
            static fn (HookDefinition $a, HookDefinition $b): int => $b->priority <=> $a->priority,
        );

        return $hooks;
    }
}
