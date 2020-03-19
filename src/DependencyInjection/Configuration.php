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
                        ->scalarNode('recovery_endpoint')->defaultValue('')->end()
                        ->arrayNode('http_signature')
                            ->children()
                                ->scalarNode('secret_key')->end()
                                ->enumNode('algorithm')
                                    ->values(['hmac-sha256', 'hmac-sha1'])
                                    ->defaultValue('hmac-sha256')
                                ->end()
                                ->arrayNode('headers')
                                    ->scalarPrototype()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end() // event

                ->arrayNode('validation')
                    ->children()
                        ->booleanNode('disable_on_dev_mode')->defaultFalse()->end()
                        ->scalarNode('schema_base_path')->defaultValue('%kernel.project_dir%/vendor/ipedis/rabbit-client-bundle/Resources/schema')->end()
                    ->end()
                ->end() // validation
            ->end()
        ;

        return $treeBuilder;
    }
}
