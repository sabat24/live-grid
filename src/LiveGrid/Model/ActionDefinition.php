<?php

namespace App\LiveGrid\Model;

final readonly class ActionDefinition
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public string $name,
        public string $type,
        public string $group,
        public ?string $label = null,
        public bool $enabled = true,
        public int $position = 0,
        public array $options = [],
    ) {
    }
}
