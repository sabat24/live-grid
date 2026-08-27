<?php

namespace App\Queryable\Twig;

use Symfony\UX\TwigComponent\ComponentAttributes;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class QueryableExtension extends AbstractExtension
{
    /**
     * @return list<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('queryable_live_attributes', $this->queryableLiveAttributes(...)),
        ];
    }

    /**
     * Merge Stimulus `queryable` controller onto Live Component attributes so any
     * Twig template can opt into URL sync without copying stimulus_controller wiring.
     *
     * `data-controller` is prepended (Symfony UX special-casing), so existing controllers remain.
     */
    public function queryableLiveAttributes(ComponentAttributes $attributes): ComponentAttributes
    {
        return $attributes->defaults([
            'data-controller' => 'queryable',
        ]);
    }
}
