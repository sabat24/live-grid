<?php

namespace App\LiveGrid\DataSource;

use App\LiveGrid\Model\Criteria;
use App\LiveGrid\Model\Page;
use App\LiveGrid\Port\DataSourceInterface;

final class InMemoryDataSource implements DataSourceInterface
{
    /**
     * @param list<mixed> $items
     */
    public function __construct(
        private readonly array $items,
    ) {
    }

    public function paginate(Criteria $criteria): Page
    {
        $total = \count($this->items);
        $maxPerPage = max(1, $criteria->perPage);
        $pageCount = max(1, (int) ceil($total / $maxPerPage));
        $currentPage = min(max(1, $criteria->page), $pageCount);
        $offset = ($currentPage - 1) * $maxPerPage;
        $slice = \array_slice($this->items, $offset, $maxPerPage);

        return new Page($slice, $total, $currentPage, $maxPerPage);
    }
}
