<?php

namespace App\LiveGrid\Configuration;

use App\LiveGrid\Model\LiveGridConfiguration;
use App\LiveGrid\Port\LiveGridConfigurationRegistryInterface;

final class LiveGridConfigurationRegistry implements LiveGridConfigurationRegistryInterface
{
    /**
     * @param array<string, LiveGridConfiguration> $configurations
     */
    public function __construct(
        private array $configurations = [],
    ) {
    }

    /**
     * @param array<string, array{
     *     adapter: string,
     *     filter_form?: string|null,
     *     template?: string|null,
     *     options?: array<string, mixed>,
     *     hooks?: array<string, array<string, array{template: string, priority?: int}>>
     * }> $grids
     */
    public function loadFromArray(array $grids): void
    {
        foreach ($grids as $name => $config) {
            $this->configurations[$name] = new LiveGridConfiguration(
                name: $name,
                adapter: $config['adapter'],
                filterForm: $config['filter_form'] ?? null,
                template: $config['template'] ?? null,
                options: $config['options'] ?? [],
                hooks: $config['hooks'] ?? [],
            );
        }
    }

    public function register(LiveGridConfiguration $configuration): void
    {
        $this->configurations[$configuration->name] = $configuration;
    }

    public function get(string $name): LiveGridConfiguration
    {
        if (!isset($this->configurations[$name])) {
            throw new \InvalidArgumentException(sprintf('Live grid "%s" is not configured.', $name));
        }

        return $this->configurations[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->configurations[$name]);
    }

    public function names(): array
    {
        return array_keys($this->configurations);
    }
}
