<?php


namespace Ipedis\Bundle\Rabbit\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('rabbit');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('connection')
                    ->children()
                        ->scalarNode('host')->end()
                        ->integerNode('port')->end()
                        ->scalarNode('user')->end()
                        ->scalarNode('password')->end()
                    ->end()
                ->end() // connection

                ->arrayNode('order')
                    ->children()
                        ->scalarNode('exchange')->end()
                        ->scalarNode('type')->end()
                    ->end()
                ->end() // order

                ->arrayNode('event')
                    ->children()
                        ->scalarNode('exchange')->end()
                        ->scalarNode('type')->end()
                    ->end()
                ->end() // event
            ->end()
        ;

        return $treeBuilder;
    }
}