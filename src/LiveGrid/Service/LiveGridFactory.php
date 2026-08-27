<?php

namespace App\LiveGrid\Service;

use App\LiveGrid\Event\LiveGridBuildCriteriaEvent;
use App\LiveGrid\Event\LiveGridInitializeEvent;
use App\LiveGrid\Event\LiveGridViewEvent;
use App\LiveGrid\Model\Criteria;
use App\LiveGrid\Model\LiveGridConfiguration;
use App\LiveGrid\Model\LiveGridView;
use App\LiveGrid\Port\GridAdapterInterface;
use App\LiveGrid\Port\LiveGridConfigurationRegistryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Orchestrates configuration lookup, events, and adapter calls into a LiveGridView.
 */
final class LiveGridFactory
{
    public function __construct(
        private readonly LiveGridConfigurationRegistryInterface $configurationRegistry,
        private readonly GridAdapterInterface $gridAdapter,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function getConfiguration(string $name): LiveGridConfiguration
    {
        $configuration = $this->configurationRegistry->get($name);
        $event = new LiveGridInitializeEvent($configuration);
        $this->eventDispatcher->dispatch($event);

        return $event->getConfiguration();
    }

    /**
     * @param array<string, mixed> $context
     */
    public function createView(
        string $name,
        int $page,
        int $resultsPerPage,
        ?object $filterForm = null,
        array $context = [],
    ): LiveGridView {
        $configuration = $this->getConfiguration($name);

        $criteria = new Criteria(page: $page, perPage: $resultsPerPage);
        $criteriaEvent = new LiveGridBuildCriteriaEvent($configuration, $criteria);
        $this->eventDispatcher->dispatch($criteriaEvent);
        $criteria = $criteriaEvent->getCriteria();

        $definition = $this->gridAdapter->getDefinition($configuration);
        $pageResult = $this->gridAdapter->getPage($configuration, $criteria, $filterForm);

        $view = new LiveGridView(
            gridName: $name,
            definition: $definition,
            page: $pageResult,
            criteria: new Criteria(
                page: $pageResult->currentPage,
                perPage: $pageResult->maxPerPage,
                sort: $criteria->sort,
                direction: $criteria->direction,
                filters: $criteria->filters,
            ),
            context: $context,
        );

        $viewEvent = new LiveGridViewEvent($configuration, $view);
        $this->eventDispatcher->dispatch($viewEvent);

        return $viewEvent->getView();
    }
}
