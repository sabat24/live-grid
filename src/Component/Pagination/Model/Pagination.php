<?php

namespace App\Component\Pagination\Model;

final class Pagination
{
    /** @var array<string, mixed> */
    protected array $defaultOptions = [
        self::PAGE_PARAMETER_NAME => 'page',
        self::SORT_FIELD_PARAMETER_NAME => 'sort',
        self::SORT_DIRECTION_PARAMETER_NAME => 'direction',
        self::FILTER_FIELD_PARAMETER_NAME => 'filterParam',
        self::FILTER_VALUE_PARAMETER_NAME => 'filterValue',
        self::DISTINCT => true,
        self::PAGE_OUT_OF_RANGE => self::PAGE_OUT_OF_RANGE_IGNORE,
        self::DEFAULT_LIMIT => self::DEFAULT_LIMIT_VALUE,
    ];

    public const DEFAULT_SORT_FIELD_NAME = 'defaultSortFieldName';
    public const DEFAULT_SORT_DIRECTION = 'defaultSortDirection';
    public const DEFAULT_FILTER_FIELDS = 'defaultFilterFields';
    public const SORT_FIELD_PARAMETER_NAME = 'sortFieldParameterName';
    public const SORT_FIELD_ALLOW_LIST = 'sortFieldAllowList';
    public const SORT_DIRECTION_PARAMETER_NAME = 'sortDirectionParameterName';
    public const PAGE_PARAMETER_NAME = 'pageParameterName';
    public const FILTER_FIELD_PARAMETER_NAME = 'filterFieldParameterName';
    public const FILTER_VALUE_PARAMETER_NAME = 'filterValueParameterName';
    public const FILTER_FIELD_ALLOW_LIST = 'filterFieldAllowList';
    public const DISTINCT = 'distinct';
    public const PAGE_OUT_OF_RANGE = 'pageOutOfRange';
    public const DEFAULT_LIMIT = 'defaultLimit';

    public const PAGE_OUT_OF_RANGE_IGNORE = 'ignore'; // do nothing (default)
    public const PAGE_OUT_OF_RANGE_FIX = 'fix'; // replace page number out of range with max page
    public const PAGE_OUT_OF_RANGE_THROW_EXCEPTION = 'throwException'; // throw PageNumberOutOfRangeException
    public const DEFAULT_LIMIT_VALUE = 10;

    /**
     * @param array<string, mixed> $options
     */
    public function paginate(
        int $totalCount,
        int $page = 1,
        ?int $limit = null,
        array $options = [],
    ): SlidingPaginationView {
        $defaultLimit = $this->defaultOptions[self::DEFAULT_LIMIT];
        if (!is_int($defaultLimit)) {
            throw new \LogicException(
                sprintf('Default limit must be an integer, %s given.', get_debug_type($defaultLimit)),
            );
        }

        $limit = $limit ?? $defaultLimit;
        if ($limit <= 0 || $page <= 0) {
            throw new \LogicException(
                "Invalid item per page number. Limit: $limit and Page: $page, must be positive non-zero integers",
            );
        }

        $options = array_merge($this->defaultOptions, $options);

        // normalize default sort field
        $defaultSortField = $options[self::DEFAULT_SORT_FIELD_NAME] ?? null;
        if (is_array($defaultSortField)) {
            $options[self::DEFAULT_SORT_FIELD_NAME] = implode('+', array_map(static function (mixed $part): string {
                if (is_string($part)) {
                    return $part;
                }

                if (is_scalar($part)) {
                    return (string) $part;
                }

                throw new \InvalidArgumentException('Default sort field parts must be scalar values.');
            }, $defaultSortField));
        }

        if ($page > (int) ceil($totalCount / $limit)) {
            $pageOutOfRangeOption = $options[self::PAGE_OUT_OF_RANGE] ?? $this->defaultOptions[self::PAGE_OUT_OF_RANGE];
            if ($pageOutOfRangeOption === self::PAGE_OUT_OF_RANGE_FIX && $totalCount > 0) {
                return $this->paginate($totalCount, (int) ceil($totalCount / $limit), $limit, $options);
            }
            if ($pageOutOfRangeOption === self::PAGE_OUT_OF_RANGE_THROW_EXCEPTION) {
                //throw new PageNumberOutOfRangeException("Page number: $page is out of range.");
            }
        }

        $paginationView = new SlidingPaginationView();
        $paginationView->setCurrentPageNumber($page);
        $paginationView->setItemNumberPerPage($limit);
        $paginationView->setTotalItemCount($totalCount);
        $paginationView->setPaginatorOptions($options);

        return $paginationView;
    }
}
