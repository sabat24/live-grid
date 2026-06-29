<?php

namespace App\Component\Grid\Service;

use App\Component\Grid\Model\GridComponentInterface;
use App\Component\User\Entity\User;
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
use Symfony\Component\HttpFoundation\Request;
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
        $request = $this->getCurrentRequest();
        $this->grid = $this->gridProvider->get($gridName);
        $resourceClass = $this->grid->getDriverConfiguration()['class'] ?? null;
        if (!is_string($resourceClass)) {
            throw new \LogicException(
                sprintf('Grid "%s" is missing a resource class in driver configuration.', $gridName),
            );
        }

        $metadata = $this->registry->getByClass($resourceClass);

        $syliusParams = $request->attributes->get('_sylius', []);
        if (!is_array($syliusParams)) {
            $syliusParams = [];
        }

        $componentConfiguration = $this->liveComponent->getConfiguration();
        $params = array_merge($syliusParams, $componentConfiguration);
        $request->attributes->set('_sylius', $params);

        $this->configuration = $this->requestConfigurationFactory->create($metadata, $request);

        $parameters = new Parameters($request->query->all());
        $this->gridView = $this->gridViewFactory->create(
            $this->grid,
            $parameters,
            $this->configuration->getMetadata(),
            $this->configuration,
        );
    }

    /**
     * @return Pagerfanta<User>
     */
    public function createPaginator(int $page, int $resultsPerPage): Pagerfanta
    {
        $filterQueryBuilder = $this->liveComponent->createFilterQueryBuilder();

        if ($this->configuration->isFilterable()) {
            $form = $this->liveComponent->getSearchFormInstance();
            $this->filterBuilderUpdater->addFilterConditions($form, $filterQueryBuilder);
        }

        /** @var Pagerfanta<User> $paginator */
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

    /**
     * @return list<int>
     */
    public function getAllowedPaginate(): array
    {
        $allowed = $this->configuration->getParameters()->get('allowed_paginate');
        if (!is_array($allowed)) {
            return [];
        }

        return array_values(
            array_filter(
                array_map(static function (mixed $value): ?int {
                    if (!is_numeric($value)) {
                        return null;
                    }

                    return (int) $value;
                }, $allowed),
                static fn(?int $value): bool => $value !== null,
            ),
        );
    }

    public function getGridView(): GridView
    {
        return $this->gridView;
    }

    private function getCurrentRequest(): Request
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            throw new \RuntimeException('Grid live components require an active HTTP request.');
        }

        return $request;
    }
}
