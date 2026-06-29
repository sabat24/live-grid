<?php

namespace App\Bundles\KnpMenuBundle\Voter;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestParentRouteVoter implements VoterInterface
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function matchItem(ItemInterface $item): ?bool
    {
        $request = $this->requestStack->getMainRequest();
        if (!$request instanceof Request) {
            return null;
        }

        $requestRoute = $request->attributes->get('_route');
        if (!is_string($requestRoute)) {
            return null;
        }

        $itemRoute = $item->getExtra('routes');
        if ($itemRoute === null) {
            return null;
        }

        if ($requestRoute === $itemRoute) {
            return true;
        }

        $parentRoutePattern = $item->getExtra('parentRoutePattern');
        if (!is_string($parentRoutePattern)) {
            return null;
        }

        if (str_starts_with($requestRoute, $parentRoutePattern)) {
            return true;
        }

        return null;
    }
}
