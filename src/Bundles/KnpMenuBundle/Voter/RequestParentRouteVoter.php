<?php

namespace App\Bundles\KnpMenuBundle\Voter;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestParentRouteVoter implements VoterInterface
{

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function matchItem(ItemInterface $item): ?bool
    {
        $requestRoute = $this->requestStack->getMainRequest()->get('_route');
        $itemRoute = $item->getExtra('routes');
        if ($itemRoute === null) {
            return null;
        }

        if ($requestRoute === $itemRoute) {
            return true;
        }

        $parentRoutePattern = $item->getExtra('parentRoutePattern');

        if ($parentRoutePattern === null) {
            return null;
        }

        if (str_starts_with($requestRoute, $parentRoutePattern)) {
            return true;
        }

        return null;
    }

}


