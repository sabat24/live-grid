<?php

namespace App\Component\Pagination\Model;

final class SlidingPaginationView extends AbstractPaginationView
{
    private int $pageRange = 5;

    public function getPaginationData(): ViewData
    {
        $pageCount = $this->getPageCount();
        $current = $this->currentPageNumber;

        if ($pageCount < $current) {
            $this->currentPageNumber = $current = $pageCount;
        }

        if ($this->pageRange > $pageCount) {
            $this->pageRange = $pageCount;
        }

        $delta = (int) \ceil($this->pageRange / 2);

        if ($current - $delta > $pageCount - $this->pageRange) {
            $pages = \range($pageCount - $this->pageRange + 1, $pageCount);
        } else {
            if ($current - $delta < 0) {
                $delta = $current;
            }

            $offset = $current - $delta;
            $pages = \range($offset + 1, $offset + $this->pageRange);
        }

        $proximity = (int) \floor($this->pageRange / 2);

        $startPage = $current - $proximity;
        $endPage = $current + $proximity;

        if ($startPage < 1) {
            $endPage = \min($endPage + (1 - $startPage), $pageCount);
            $startPage = 1;
        }

        if ($endPage > $pageCount) {
            $startPage = \max($startPage - ($endPage - $pageCount), 1);
            $endPage = $pageCount;
        }

        $viewData = new ViewData();
        $viewData->last = $pageCount;
        $viewData->current = $current;
        $viewData->numItemsPerPage = $this->numItemsPerPage;
        $viewData->first = 1;
        $viewData->pageCount = $pageCount;
        $viewData->totalCount = $this->totalCount;
        $viewData->pageRange = $this->pageRange;
        $viewData->startPage = $startPage;
        $viewData->endPage = $endPage;

        if ($current > 1) {
            $viewData->previous = $current - 1;
        }

        if ($current < $pageCount) {
            $viewData->next = $current + 1;
        }

        $viewData->pagesInRange = array_map('intval', $pages);
        $viewData->firstPageInRange = \min($pages);
        $viewData->lastPageInRange = \max($pages);

        return $viewData;
    }

    public function getPageCount(): int
    {
        return (int) \ceil($this->totalCount / $this->numItemsPerPage);
    }
}
