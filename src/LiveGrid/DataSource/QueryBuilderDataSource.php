<?php

namespace App\LiveGrid\DataSource;

use App\LiveGrid\Model\Criteria;
use App\LiveGrid\Model\Page;
use App\LiveGrid\Port\QueryBuilderAwareDataSourceInterface;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\LessThan1CurrentPageException;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;

final class QueryBuilderDataSource implements QueryBuilderAwareDataSourceInterface
{
    public function __construct(
        private readonly QueryBuilder $queryBuilder,
    ) {
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function paginate(Criteria $criteria): Page
    {
        /** @var Pagerfanta<mixed> $paginator */
        $paginator = new Pagerfanta(new QueryAdapter($this->queryBuilder, false, false));
        try {
            $paginator->setMaxPerPage($criteria->perPage)->setCurrentPage($criteria->page);
        } catch (LessThan1CurrentPageException) {
            $paginator->setCurrentPage(1);
        } catch (OutOfRangeCurrentPageException) {
            $paginator->setCurrentPage($paginator->getNbPages());
        }

        return new Page(
            $paginator->getCurrentPageResults(),
            $paginator->getNbResults(),
            $paginator->getCurrentPage(),
            $paginator->getMaxPerPage(),
        );
    }
}
