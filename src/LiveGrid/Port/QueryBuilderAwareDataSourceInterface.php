<?php

namespace App\LiveGrid\Port;

use Doctrine\ORM\QueryBuilder;

interface QueryBuilderAwareDataSourceInterface extends DataSourceInterface
{
    public function getQueryBuilder(): QueryBuilder;
}
