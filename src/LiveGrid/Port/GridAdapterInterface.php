<?php

namespace App\LiveGrid\Port;

use App\LiveGrid\Model\Criteria;
use App\LiveGrid\Model\GridDefinition;
use App\LiveGrid\Model\LiveGridConfiguration;
use App\LiveGrid\Model\Page;

/**
 * Stateless adapter that resolves grid definition and data for a named configuration.
 */
interface GridAdapterInterface
{
    public function supports(LiveGridConfiguration $configuration): bool;

    public function getDefinition(LiveGridConfiguration $configuration): GridDefinition;

    public function getPage(LiveGridConfiguration $configuration, Criteria $criteria, ?object $filterForm = null): Page;
}
