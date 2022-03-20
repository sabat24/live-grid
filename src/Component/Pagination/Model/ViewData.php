<?php

namespace App\Component\Pagination\Model;

final class ViewData
{
    public int $last;
    public int $current;
    public int $numItemsPerPage;
    public int $first;
    public int $pageCount;
    public int $totalCount;
    public int $pageRange;
    public int $startPage;
    public int $endPage;
    public array $pagesInRange;
    public int $firstPageInRange;
    public int $lastPageInRange;
    public ?int $next = null;
    public ?int $previous = null;
}
