<?php

namespace App\LiveGrid\Model;

/**
 * Immutable criteria for fetching a page of grid data.
 * Sorting fields are reserved for adapters; core LiveGrid does not wire live sorting yet.
 */
final readonly class Criteria
{
    /**
     * @param array<string, mixed> $filters
     */
    public function __construct(
        public int $page = 1,
        public int $perPage = 10,
        public ?string $sort = null,
        public ?string $direction = null,
        public array $filters = [],
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function withFilters(array $filters): self
    {
        return new self($this->page, $this->perPage, $this->sort, $this->direction, $filters);
    }

    public function withPage(int $page): self
    {
        return new self($page, $this->perPage, $this->sort, $this->direction, $this->filters);
    }

    public function withPerPage(int $perPage): self
    {
        return new self($this->page, $perPage, $this->sort, $this->direction, $this->filters);
    }
}
