<?php

namespace App\LiveGrid\Model;

/**
 * Resolved configuration for a named live grid (from YAML + defaults).
 */
final readonly class LiveGridConfiguration
{
    /**
     * @param string|null $filterForm Form type FQCN
     * @param array<string, mixed> $options Adapter-specific options
     * @param array<string, array<string, array{template: string, priority?: int}>> $hooks
     */
    public function __construct(
        public string $name,
        public string $adapter,
        public ?string $filterForm = null,
        public ?string $template = null,
        public array $options = [],
        public array $hooks = [],
    ) {
    }
}
