<?php

namespace App\Adapter\Sylius\Adapter;

use App\LiveGrid\DataSource\QueryBuilderDataSource;
use App\LiveGrid\Filter\SpiriitFilterHandler;
use App\LiveGrid\Model\ActionDefinition;
use App\LiveGrid\Model\Criteria;
use App\LiveGrid\Model\FieldDefinition;
use App\LiveGrid\Model\GridDefinition;
use App\LiveGrid\Model\LiveGridConfiguration;
use App\LiveGrid\Model\Page;
use App\LiveGrid\Port\GridAdapterInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Bundle\ResourceBundle\Grid\View\ResourceGridViewFactoryInterface;
use Sylius\Component\Grid\Definition\Action;
use Sylius\Component\Grid\Definition\Field;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;
use Sylius\Component\Grid\Provider\GridProviderInterface;
use Sylius\Component\Grid\View\GridView;
use Sylius\Resource\Metadata\MetadataInterface;
use Sylius\Resource\Metadata\RegistryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Stateless Sylius Grid + Resource adapter for LiveGrid.
 */
final class SyliusGridAdapter implements GridAdapterInterface
{
    public function __construct(
        private readonly GridProviderInterface $gridProvider,
        private readonly RegistryInterface $registry,
        private readonly RequestConfigurationFactoryInterface $requestConfigurationFactory,
        private readonly ResourceGridViewFactoryInterface $gridViewFactory,
        private readonly RequestStack $requestStack,
        private readonly ManagerRegistry $managerRegistry,
        private readonly SpiriitFilterHandler $filterHandler,
    ) {
    }

    public function supports(LiveGridConfiguration $configuration): bool
    {
        return $configuration->adapter === 'sylius';
    }

    public function getDefinition(LiveGridConfiguration $configuration): GridDefinition
    {
        [$syliusGrid, $gridView, $requestConfiguration] = $this->resolveSylius($configuration);

        return $this->mapDefinition($configuration->name, $syliusGrid, $gridView, $requestConfiguration);
    }

    public function getPage(LiveGridConfiguration $configuration, Criteria $criteria, ?object $filterForm = null): Page
    {
        [$syliusGrid, $gridView, $requestConfiguration] = $this->resolveSylius($configuration);

        $queryBuilder = $this->createQueryBuilder($syliusGrid);
        $dataSource = new QueryBuilderDataSource($queryBuilder);

        if (
            $requestConfiguration->isFilterable()
            && $filterForm instanceof FormInterface
            && $this->filterHandler->supports($dataSource)
        ) {
            $this->filterHandler->apply($filterForm, $dataSource);
        }

        return $dataSource->paginate($criteria);
    }

    /**
     * @return array{0: Grid, 1: GridView, 2: RequestConfiguration}
     */
    private function resolveSylius(LiveGridConfiguration $configuration): array
    {
        $gridName = $configuration->options['grid'] ?? null;
        if (!is_string($gridName) || $gridName === '') {
            throw new \InvalidArgumentException(sprintf(
                'Sylius LiveGrid "%s" requires options.grid (Sylius grid name).',
                $configuration->name,
            ));
        }

        $request = $this->getCurrentRequest();
        $syliusGrid = $this->gridProvider->get($gridName);
        $resourceClass = $syliusGrid->getDriverConfiguration()['class'] ?? null;
        if (!is_string($resourceClass)) {
            throw new \LogicException(sprintf(
                'Grid "%s" is missing a resource class in driver configuration.',
                $gridName,
            ));
        }

        $metadata = $this->registry->getByClass($resourceClass);

        // Clone attribute bag merge locally — do not permanently mutate shared request state beyond this request's _sylius.
        $syliusParams = $request->attributes->get('_sylius', []);
        if (!is_array($syliusParams)) {
            $syliusParams = [];
        }

        $componentConfiguration = [
            'filterable' => (bool) ($configuration->options['filterable'] ?? true),
            'section' => $configuration->options['section'] ?? ($syliusParams['section'] ?? 'admin'),
        ];
        if (isset($configuration->options['default_page_size'])) {
            $componentConfiguration['default_page_size'] = $configuration->options['default_page_size'];
        }

        $merged = array_merge($syliusParams, $componentConfiguration);
        $request->attributes->set('_sylius', $merged);

        $requestConfiguration = $this->requestConfigurationFactory->create($metadata, $request);
        $parameters = new Parameters($request->query->all());
        $gridView = $this->gridViewFactory->create(
            $syliusGrid,
            $parameters,
            $requestConfiguration->getMetadata(),
            $requestConfiguration,
        );

        return [$syliusGrid, $gridView, $requestConfiguration];
    }

    private function mapDefinition(
        string $name,
        Grid $syliusGrid,
        GridView $gridView,
        RequestConfiguration $requestConfiguration,
    ): GridDefinition {
        $fields = [];
        $position = 0;
        /** @var Field $field */
        foreach ($syliusGrid->getFields() as $field) {
                $fields[] = new FieldDefinition(
                    name: $field->getName(),
                    type: $field->getType(),
                    label: $field->getLabel(),
                    path: $field->getPath(),
                    enabled: $field->isEnabled(),
                    sortable: $field->isSortable(),
                    position: $position++,
                    options: $field->getOptions(),
                );
        }

        $actions = [];
        $actionPosition = 0;
        foreach (array_keys($syliusGrid->getActionGroups()) as $groupName) {
            /** @var Action $action */
            foreach ($syliusGrid->getActions($groupName) as $action) {
                $actions[] = new ActionDefinition(
                    name: $action->getName(),
                    type: $action->getType(),
                    group: $groupName,
                    label: $action->getLabel(),
                    enabled: $action->isEnabled(),
                    position: $actionPosition++,
                    options: $action->getOptions(),
                );
            }
        }

        $allowed = $requestConfiguration->getParameters()->get('allowed_paginate', [10, 25, 50]);
        if (!is_array($allowed)) {
            $allowed = [10, 25, 50];
        }

        $allowedPaginate = [];
        foreach ($allowed as $value) {
            if (is_numeric($value)) {
                $allowedPaginate[] = (int) $value;
            }
        }

        return new GridDefinition(
            name: $name,
            fields: $fields,
            actions: $actions,
            allowedPaginate: $allowedPaginate !== [] ? $allowedPaginate : [10, 25, 50],
            filterable: $requestConfiguration->isFilterable(),
            extras: [
                'sylius_grid_view' => $gridView,
                'sylius_grid' => $syliusGrid,
                'request_configuration' => $requestConfiguration,
            ],
        );
    }

    private function createQueryBuilder(Grid $syliusGrid): \Doctrine\ORM\QueryBuilder
    {
        $resourceClass = $syliusGrid->getDriverConfiguration()['class'] ?? null;
        if (!is_string($resourceClass)) {
            throw new \LogicException('Cannot create query builder without resource class.');
        }

        $metadata = $this->registry->getByClass($resourceClass);
        $repository = $this->resolveRepository($metadata);

        if (method_exists($repository, 'createQueryBuilder')) {
            /** @var \Doctrine\ORM\QueryBuilder $qb */
            $qb = $repository->createQueryBuilder('o');

            return $qb;
        }

        throw new \LogicException(sprintf(
            'Repository for "%s" does not support createQueryBuilder().',
            $resourceClass,
        ));
    }

    private function resolveRepository(MetadataInterface $metadata): object
    {
        $serviceId = $metadata->getServiceId('repository');
        // Prefer Doctrine manager repository when Sylius service id is not directly available
        try {
            $manager = $this->managerRegistry->getManagerForClass($metadata->getClass('model'));
            if ($manager !== null) {
                /** @var EntityRepository<object> $repo */
                $repo = $manager->getRepository($metadata->getClass('model'));

                return $repo;
            }
        } catch (\Throwable) {
            // fall through
        }

        throw new \LogicException(sprintf(
            'Unable to resolve repository for resource "%s" (expected service "%s").',
            $metadata->getAlias(),
            $serviceId,
        ));
    }

    private function getCurrentRequest(): Request
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            throw new \RuntimeException('Sylius LiveGrid adapter requires an active HTTP request.');
        }

        return $request;
    }
}
