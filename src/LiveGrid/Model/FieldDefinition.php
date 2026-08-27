<?php

namespace App\LiveGrid\Model;

final readonly class FieldDefinition
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public string $name,
        public string $type,
        public string $label,
        public string $path,
        public bool $enabled = true,
        public bool $sortable = false,
        public int $position = 0,
        public array $options = [],
    ) {
    }
}
