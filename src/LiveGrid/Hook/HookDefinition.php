<?php

namespace App\LiveGrid\Hook;

final readonly class HookDefinition
{
    public function __construct(
        public string $id,
        public string $template,
        public int $priority = 0,
    ) {
    }
}
