<?php

namespace App\LiveGrid\Event;

use App\LiveGrid\Model\Criteria;
use App\LiveGrid\Model\LiveGridConfiguration;
use Symfony\Contracts\EventDispatcher\Event;

final class LiveGridBuildCriteriaEvent extends Event
{
    public function __construct(
        private readonly LiveGridConfiguration $configuration,
        private Criteria $criteria,
    ) {
    }

    public function getConfiguration(): LiveGridConfiguration
    {
        return $this->configuration;
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }

    public function setCriteria(Criteria $criteria): void
    {
        $this->criteria = $criteria;
    }
}
