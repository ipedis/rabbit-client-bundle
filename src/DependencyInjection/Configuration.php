<?php


namespace Ipedis\Bundle\Rabbit\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('ipedis_rabbit');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('protocol_version')->defaultValue('v1')->end()
                ->scalarNode('service_name')->end()
            ->end()
            ->children()
                ->arrayNode('connection')
                    ->children()
                        ->scalarNode('host')->defaultValue('localhost')->end()
                        ->integerNode('port')->defaultValue(5672)->end()
                        ->scalarNode('user')->defaultValue('guest')->end()
                        ->scalarNode('password')->defaultValue('guest')->end()
                    ->end()
                ->end() // connection

                ->arrayNode('order')
                    ->children()
                        ->scalarNode('exchange')->defaultValue('publispeak_orders')->end()
                        ->scalarNode('type')->defaultValue('topic')->end()
                    ->end()
                ->end() // order

                ->arrayNode('event')
                    ->children()
                        ->scalarNode('exchange')->defaultValue('publispeak_events')->end()
                        ->scalarNode('type')->defaultValue('topic')->end()
                    ->end()
                ->end() // event

                ->arrayNode('validation')
                    ->children()
                        ->scalarNode('schema_base_path')->end()
                    ->end()
                ->end() // validation
            ->end()
        ;

        return $treeBuilder;
    }
}
