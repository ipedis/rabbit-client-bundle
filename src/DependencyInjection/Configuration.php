<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Rabbit\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ipedis_rabbit');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('protocol_version')->defaultValue('v1')->end()
                ->scalarNode('service_name')->isRequired()->cannotBeEmpty()->end()
            ->end()
            ->children()
                ->arrayNode('connection')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('host')->defaultValue('localhost')->end()
                        ->integerNode('port')->defaultValue(5672)->end()
                        ->scalarNode('user')->defaultValue('guest')->end()
                        ->scalarNode('password')->defaultValue('guest')->end()
                    ->end()
                ->end() // connection

                ->arrayNode('order')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('exchange')->defaultValue('publispeak_orders')->end()
                        ->scalarNode('type')->defaultValue('topic')->end()
                        ->scalarNode('env')->defaultValue('dev')->end()
                    ->end()
                ->end() // order

                ->arrayNode('event')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('exchange')->defaultValue('publispeak_events')->end()
                        ->scalarNode('type')->defaultValue('topic')->end()
                        // The recovery fallback is gone: both keys are still accepted so that existing
                        // configurations keep booting, but they are ignored. To remove in 3.0.
                        ->scalarNode('recovery_endpoint')
                            ->setDeprecated('ipedis/rabbit-client-bundle', '2.2', 'The "%node%" option at path "%path%" is ignored since the recovery fallback was removed; remove it from your configuration.')
                        ->end()
                        ->variableNode('http_signature')
                            ->setDeprecated('ipedis/rabbit-client-bundle', '2.2', 'The "%node%" option at path "%path%" is ignored since the recovery fallback was removed; remove it from your configuration.')
                        ->end()
                    ->end()
                ->end() // event

                ->arrayNode('validation')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('disable_on_dev_mode')->defaultFalse()->end()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->scalarNode('schema_base_path')->defaultValue('%kernel.project_dir%/vendor/ipedis/rabbit-client-bundle/Resources/schema')->end()
                    ->end()
                ->end() // validation
            ->end()
        ;

        return $treeBuilder;
    }
}
