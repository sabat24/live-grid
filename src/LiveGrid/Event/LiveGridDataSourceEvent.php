<?php

namespace App\LiveGrid\Event;

use App\LiveGrid\Model\Criteria;
use App\LiveGrid\Model\LiveGridConfiguration;
use App\LiveGrid\Port\DataSourceInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class LiveGridDataSourceEvent extends Event
{
    public function __construct(
        private readonly LiveGridConfiguration $configuration,
        private readonly Criteria $criteria,
        private ?DataSourceInterface $dataSource = null,
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

    public function getDataSource(): ?DataSourceInterface
    {
        return $this->dataSource;
    }

    public function setDataSource(DataSourceInterface $dataSource): void
    {
        $this->dataSource = $dataSource;
    }
}
