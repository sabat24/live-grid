<?php

namespace App\Tests\Component;

use App\Component\LiveComponent\Service\QueryableParamsBuilder;
use App\Tests\ContainerTestTrait;
use App\Tests\Fixtures\Component\QueryableTestComponent;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class QueryableComponentTraitTest extends KernelTestCase
{
    use ContainerTestTrait;

    public function testMountHydratesQueryablePropsFromRequestQuery(): void
    {
        $container = $this->getTestContainer();

        $request = Request::create('/', 'GET', [
            'test_queryable' => ['page' => 2],
        ]);

        $requestStack = $container->get(RequestStack::class);
        $requestStack->push($request);

        $component = new QueryableTestComponent(
            $requestStack,
            $container->get(QueryableParamsBuilder::class),
        );
        $component->updatePropsFromRequest([]);

        self::assertSame(2, $component->page);
        self::assertSame(10, $component->resultsPerPage);
        self::assertSame('test_queryable', $component->componentName);
    }

    public function testWindowQueryStringRestoresPropsAfterHistoryChange(): void
    {
        $container = $this->getTestContainer();
        $requestStack = $container->get(RequestStack::class);
        $requestStack->push(Request::create('/'));

        $component = new QueryableTestComponent(
            $requestStack,
            $container->get(QueryableParamsBuilder::class),
        );
        $component->updatePropsFromRequest([]);
        $component->page = 3;
        $component->windowQueryString = http_build_query([
            'test_queryable' => ['page' => 2],
        ]);

        $component->updatePropsAfterHistoryChanges();

        self::assertSame(2, $component->page);
    }

    public function testWindowQueryStringWithoutComponentKeyResetsToDefaults(): void
    {
        $container = $this->getTestContainer();
        $requestStack = $container->get(RequestStack::class);
        $requestStack->push(Request::create('/'));

        $component = new QueryableTestComponent(
            $requestStack,
            $container->get(QueryableParamsBuilder::class),
        );
        $component->updatePropsFromRequest([]);
        $component->page = 3;
        $component->resultsPerPage = 25;
        $component->windowQueryString = '';

        $component->updatePropsAfterHistoryChanges();

        self::assertSame(1, $component->page);
        self::assertSame(10, $component->resultsPerPage);
    }
}
