<?php

namespace App\Tests\LiveGrid;

use App\LiveGrid\Adapter\InMemoryGridAdapter;
use App\LiveGrid\Configuration\LiveGridConfigurationRegistry;
use App\LiveGrid\DataSource\InMemoryDataSource;
use App\LiveGrid\Hook\HookDefinition;
use App\LiveGrid\Hook\HookRegistry;
use App\LiveGrid\Model\Criteria;
use App\LiveGrid\Model\LiveGridConfiguration;
use App\LiveGrid\Service\LiveGridFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class InMemoryLiveGridTest extends TestCase
{
    public function testInMemoryDataSourcePaginates(): void
    {
        $source = new InMemoryDataSource(range(1, 25));
        $page = $source->paginate(new Criteria(page: 2, perPage: 10));

        self::assertSame(25, $page->totalCount);
        self::assertSame(2, $page->currentPage);
        self::assertSame([11, 12, 13, 14, 15, 16, 17, 18, 19, 20], iterator_to_array($page->items));
    }

    public function testHookRegistrySortsByPriorityDescending(): void
    {
        $registry = new HookRegistry();
        $registry->add('live_grid.demo.toolbar', new HookDefinition('low', 'low.html.twig', 10));
        $registry->add('live_grid.demo.toolbar', new HookDefinition('high', 'high.html.twig', 100));

        $hooks = $registry->getHooks('live_grid.demo.toolbar');

        self::assertSame(['high', 'low'], array_map(static fn (HookDefinition $h): string => $h->id, $hooks));
    }

    public function testFactoryBuildsViewViaInMemoryAdapter(): void
    {
        $registry = new LiveGridConfigurationRegistry();
        $registry->register(new LiveGridConfiguration(
            name: 'demo',
            adapter: 'in_memory',
            options: [
                'items' => [
                    ['email' => 'a@example.com'],
                    ['email' => 'b@example.com'],
                    ['email' => 'c@example.com'],
                ],
                'fields' => [
                    'email' => ['type' => 'string', 'label' => 'Email', 'path' => '[email]'],
                ],
                'allowed_paginate' => [10, 25],
            ],
        ));

        $factory = new LiveGridFactory($registry, new InMemoryGridAdapter(), new EventDispatcher());
        $view = $factory->createView('demo', 1, 10);

        self::assertSame(3, $view->resourcesCount());
        self::assertCount(1, $view->definition->enabledFields());
        self::assertSame([10, 25], $view->definition->allowedPaginate);
    }
}
