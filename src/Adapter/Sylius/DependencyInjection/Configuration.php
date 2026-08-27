<?php

namespace App\Adapter\Sylius\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sylius_live_grid');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
