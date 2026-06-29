<?php

namespace App\Component\Pagination\Model;

class AbstractPaginationView
{
    protected int $currentPageNumber;
    protected int $numItemsPerPage;
    protected int $totalCount;

    /** @var array<string, mixed> */
    protected array $paginatorOptions;

    /** @var array<string, mixed> */
    protected array $customParameters;

    /**
     * @param array<string, mixed> $parameters
     */
    public function setCustomParameters(array $parameters): void
    {
        $this->customParameters = $parameters;
    }

    public function getCustomParameter(string $name): mixed
    {
        return $this->customParameters[$name] ?? null;
    }

    public function setCurrentPageNumber(int $pageNumber): void
    {
        $this->currentPageNumber = $pageNumber;
    }

    public function getCurrentPageNumber(): int
    {
        return $this->currentPageNumber;
    }

    public function setItemNumberPerPage(int $numItemsPerPage): void
    {
        $this->numItemsPerPage = $numItemsPerPage;
    }

    public function getItemNumberPerPage(): int
    {
        return $this->numItemsPerPage;
    }

    public function setTotalItemCount(int $totalCount): void
    {
        $this->totalCount = $totalCount;
    }

    public function getTotalItemCount(): int
    {
        return $this->totalCount;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setPaginatorOptions(array $options): void
    {
        $this->paginatorOptions = $options;
    }

    public function getPaginatorOption(string $name): mixed
    {
        return $this->paginatorOptions[$name] ?? null;
    }
}
