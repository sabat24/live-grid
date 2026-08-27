<?php

namespace App\LiveGrid\Pagination\Model;

use App\LiveGrid\Pagination\Exception\PageNumberOutOfRangeException;

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

    public const string DEFAULT_SORT_FIELD_NAME = 'defaultSortFieldName';
    public const string DEFAULT_SORT_DIRECTION = 'defaultSortDirection';
    public const string DEFAULT_FILTER_FIELDS = 'defaultFilterFields';
    public const string SORT_FIELD_PARAMETER_NAME = 'sortFieldParameterName';
    public const string SORT_FIELD_ALLOW_LIST = 'sortFieldAllowList';
    public const string SORT_DIRECTION_PARAMETER_NAME = 'sortDirectionParameterName';
    public const string PAGE_PARAMETER_NAME = 'pageParameterName';
    public const string FILTER_FIELD_PARAMETER_NAME = 'filterFieldParameterName';
    public const string FILTER_VALUE_PARAMETER_NAME = 'filterValueParameterName';
    public const string FILTER_FIELD_ALLOW_LIST = 'filterFieldAllowList';
    public const string DISTINCT = 'distinct';
    public const string PAGE_OUT_OF_RANGE = 'pageOutOfRange';
    public const string DEFAULT_LIMIT = 'defaultLimit';

    public const string PAGE_OUT_OF_RANGE_IGNORE = 'ignore';
    public const string PAGE_OUT_OF_RANGE_FIX = 'fix';
    public const string PAGE_OUT_OF_RANGE_THROW_EXCEPTION = 'throwException';
    public const int DEFAULT_LIMIT_VALUE = 10;

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

        $maxPage = (int) ceil($totalCount / $limit);
        if ($page > $maxPage) {
            $pageOutOfRangeOption = $options[self::PAGE_OUT_OF_RANGE] ?? $this->defaultOptions[self::PAGE_OUT_OF_RANGE];
            if ($pageOutOfRangeOption === self::PAGE_OUT_OF_RANGE_FIX && $totalCount > 0) {
                return $this->paginate($totalCount, $maxPage, $limit, $options);
            }

            if ($pageOutOfRangeOption === self::PAGE_OUT_OF_RANGE_THROW_EXCEPTION && $page > 1) {
                throw new PageNumberOutOfRangeException(
                    sprintf('Page number: %d is out of range.', $page),
                    $maxPage,
                );
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
