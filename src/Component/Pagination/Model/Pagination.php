<?php

namespace App\Component\Pagination\Model;

final class Pagination
{
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

    public function paginate(int $totalCount, int $page = 1, int $limit = null, array $options = []): SlidingPaginationView
    {
        $limit = $limit ?? $this->defaultOptions[self::DEFAULT_LIMIT];
        if ($limit <= 0 or $page <= 0) {
            throw new \LogicException("Invalid item per page number. Limit: $limit and Page: $page, must be positive non-zero integers");
        }

        $offset = ($page - 1) * $limit;
        $options = array_merge($this->defaultOptions, $options);

        // normalize default sort field
        if (isset($options[self::DEFAULT_SORT_FIELD_NAME]) && is_array($options[self::DEFAULT_SORT_FIELD_NAME])) {
            $options[self::DEFAULT_SORT_FIELD_NAME] = implode('+', $options[self::DEFAULT_SORT_FIELD_NAME]);
        }

        /*$request = null === $this->requestStack ? Request::createFromGlobals() : $this->requestStack->getCurrentRequest();

        // default sort field and direction are set based on options (if available)
        if (isset($options[self::DEFAULT_SORT_FIELD_NAME]) && !$request->query->has($options[self::SORT_FIELD_PARAMETER_NAME])) {
           $request->query->set($options[self::SORT_FIELD_PARAMETER_NAME], $options[self::DEFAULT_SORT_FIELD_NAME]);

            if (!$request->query->has($options[self::SORT_DIRECTION_PARAMETER_NAME])) {
                $request->query->set($options[self::SORT_DIRECTION_PARAMETER_NAME], $options[self::DEFAULT_SORT_DIRECTION] ?? 'asc');
            }
        }*/

        if ($page > ceil($totalCount / $limit)) {
            $pageOutOfRangeOption = $options[self::PAGE_OUT_OF_RANGE] ?? $this->defaultOptions[self::PAGE_OUT_OF_RANGE];
            if ($pageOutOfRangeOption === self::PAGE_OUT_OF_RANGE_FIX && $totalCount > 0) {
                // replace page number out of range with max page
                return $this->paginate($totalCount, ceil($totalCount / $limit), $limit, $options);
            }
            if ($pageOutOfRangeOption === self::PAGE_OUT_OF_RANGE_THROW_EXCEPTION) {
                //throw new PageNumberOutOfRangeException("Page number: $page is out of range.");
            }
        }

        // pagination class can be different, with different rendering methods
        $paginationView = new SlidingPaginationView([]);
        $paginationView->setCurrentPageNumber($page);
        $paginationView->setItemNumberPerPage($limit);
        $paginationView->setTotalItemCount($totalCount);
        $paginationView->setPaginatorOptions($options);

        return $paginationView;
    }
}
