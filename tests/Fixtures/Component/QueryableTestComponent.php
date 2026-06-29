<?php

namespace App\Tests\Fixtures\Component;

use App\Component\LiveComponent\Attribute\QueryableProp;
use App\Component\LiveComponent\Service\QueryableParamsBuilder;
use App\Component\LiveComponent\Trait\QueryableComponentTrait;
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
