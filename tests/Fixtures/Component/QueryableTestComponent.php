<?php

namespace App\Tests\Fixtures\Component;

use App\Queryable\Attribute\QueryableProp;
use App\Queryable\LiveComponent\QueryableComponentTrait;
use App\Queryable\Service\QueryableParamsBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('test_queryable', route: 'live_component_admin')]
final class QueryableTestComponent
{
    use DefaultActionTrait;
    use QueryableComponentTrait;

    #[LiveProp(writable: true)]
    #[QueryableProp]
    public int $page = 1;

    #[LiveProp(writable: true)]
    #[QueryableProp]
    public int $resultsPerPage = 10;

    public function __construct(
        private readonly RequestStack $requestStack,
        QueryableParamsBuilder $queryableParamsBuilder,
    ) {
        $this->setQueryableParamsBuilder($queryableParamsBuilder);
    }

    protected function getRequestStack(): RequestStack
    {
        return $this->requestStack;
    }
}
