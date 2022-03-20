<?php

namespace App\Component\Grid\Service;

use App\Component\Grid\Model\GridComponentInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Exception\LessThan1CurrentPageException;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Bundle\ResourceBundle\Grid\View\ResourceGridViewFactoryInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;
use Sylius\Component\Grid\Provider\ChainProvider;
use Sylius\Component\Grid\View\GridView;
use Sylius\Component\Resource\Metadata\RegistryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class GridLiveComponentService
{
    private RequestConfiguration $configuration;
    private Grid $grid;
    private GridView $gridView;

    private GridComponentInterface $liveComponent;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ChainProvider $gridProvider,
        private readonly RegistryInterface $registry,
        private readonly RequestConfigurationFactoryInterface $requestConfigurationFactory,
        private readonly FilterBuilderUpdaterInterface $filterBuilderUpdater,
        private readonly ResourceGridViewFactoryInterface $gridViewFactory,
    ) {
    }

    public function initialize(GridComponentInterface $liveComponent, string $gridName): void
    {
        $this->liveComponent = $liveComponent;
        $this->resolveConfiguration($gridName);
    }

    private function resolveConfiguration(string $gridName): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $this->grid = $this->gridProvider->get($gridName);
        $resourceClass = $this->grid->getDriverConfiguration()['class'];

        $metadata = $this->registry->getByClass($resourceClass);

        $params = $request->attributes->get('_sylius', []);
        $params = array_merge($params, $this->liveComponent->getConfiguration());
        $request->attributes->add(['_sylius' => $params]);

        $this->configuration = $this->requestConfigurationFactory->create($metadata, $request);

        $parameters = new Parameters($request->query->all());
        $this->gridView = $this->gridViewFactory->create(
            $this->grid,
            $parameters,
            $this->configuration->getMetadata(),
            $this->configuration,
        );
    }

    public function createPaginator($page, $resultsPerPage): Pagerfanta
    {
        // initialize a query builder
        $filterQueryBuilder = $this->liveComponent->createFilterQueryBuilder();

        if ($this->configuration->isFilterable()) {
            $form = $this->liveComponent->getSearchFormInstance();
            $this->filterBuilderUpdater->addFilterConditions($form, $filterQueryBuilder);
        }

        $paginator = new Pagerfanta(new QueryAdapter($filterQueryBuilder, false, false));
        try {
            $paginator->setMaxPerPage($resultsPerPage)->setCurrentPage($page);
        } catch (LessThan1CurrentPageException) {
            $paginator->setCurrentPage(1);
        } catch (OutOfRangeCurrentPageException) {
            $paginator->setCurrentPage($paginator->getNbPages());
        }

        return $paginator;
    }

    public function getAllowedPaginate(): array
    {
        return $this->configuration->getParameters()->get('allowed_paginate');
    }

    public function getGridView(): GridView
    {
        return $this->gridView;
    }
}
