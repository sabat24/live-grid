<?php

namespace App\LiveGrid\Model;

final readonly class Page
{
    /**
     * @param iterable<int, mixed> $items
     */
    public function __construct(
        public iterable $items,
        public int $totalCount,
        public int $currentPage,
        public int $maxPerPage,
    ) {
    }
}
