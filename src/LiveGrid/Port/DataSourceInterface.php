<?php

namespace App\LiveGrid\Port;

use App\LiveGrid\Model\Criteria;
use App\LiveGrid\Model\Page;

interface DataSourceInterface
{
    public function paginate(Criteria $criteria): Page;
}
