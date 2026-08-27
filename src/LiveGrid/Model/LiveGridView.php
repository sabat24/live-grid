<?php

namespace App\LiveGrid\Model;

/**
 * View model passed to Twig hooks and renderers.
 */
final readonly class LiveGridView
{
    /**
     * @param array<string, mixed> $context Extra context for hooks/templates
     */
    public function __construct(
        public string $gridName,
        public GridDefinition $definition,
        public Page $page,
        public Criteria $criteria,
        public array $context = [],
    ) {
    }

    /**
     * @return iterable<int, mixed>
     */
    public function resources(): iterable
    {
        return $this->page->items;
    }

    public function resourcesCount(): int
    {
        return $this->page->totalCount;
    }
}
