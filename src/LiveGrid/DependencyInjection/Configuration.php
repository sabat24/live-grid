<?php

namespace App\LiveGrid\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('live_grid');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->arrayNode('grids')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('adapter')->isRequired()->end()
                            ->scalarNode('filter_form')->defaultNull()->end()
                            ->scalarNode('template')->defaultNull()->end()
                            ->variableNode('options')->defaultValue([])->end()
                            ->arrayNode('hooks')
                                ->useAttributeAsKey('hook_name')
                                ->arrayPrototype()
                                    ->useAttributeAsKey('id')
                                    ->arrayPrototype()
                                        ->children()
                                            ->scalarNode('template')->isRequired()->end()
                                            ->integerNode('priority')->defaultValue(0)->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('hooks')
                    ->useAttributeAsKey('hook_name')
                    ->arrayPrototype()
                        ->useAttributeAsKey('id')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('template')->isRequired()->end()
                                ->integerNode('priority')->defaultValue(0)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
