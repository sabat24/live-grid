<?php

namespace App\LiveGrid\Adapter;

use App\LiveGrid\Model\Criteria;
use App\LiveGrid\Model\GridDefinition;
use App\LiveGrid\Model\LiveGridConfiguration;
use App\LiveGrid\Model\Page;
use App\LiveGrid\Port\GridAdapterInterface;

/**
 * Selects the first supporting adapter (tagged services, priority order).
 */
final class ChainGridAdapter implements GridAdapterInterface
{
    /**
     * @param iterable<GridAdapterInterface> $adapters
     */
    public function __construct(
        private readonly iterable $adapters,
    ) {
    }

    public function supports(LiveGridConfiguration $configuration): bool
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supports($configuration)) {
                return true;
            }
        }

        return false;
    }

    public function getDefinition(LiveGridConfiguration $configuration): GridDefinition
    {
        return $this->resolve($configuration)->getDefinition($configuration);
    }

    public function getPage(LiveGridConfiguration $configuration, Criteria $criteria, ?object $filterForm = null): Page
    {
        return $this->resolve($configuration)->getPage($configuration, $criteria, $filterForm);
    }

    private function resolve(LiveGridConfiguration $configuration): GridAdapterInterface
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supports($configuration)) {
                return $adapter;
            }
        }

        throw new \LogicException(sprintf(
            'No LiveGrid adapter supports grid "%s" (adapter key "%s").',
            $configuration->name,
            $configuration->adapter,
        ));
    }
}
