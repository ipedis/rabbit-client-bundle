<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Rabbit\DependencyInjection;

use Ipedis\Bundle\Rabbit\Service\Container\WorkerContainer;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class RabbitExtension extends Extension
{
    /**
     * @param array<array<string, mixed>> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {

        $configuration = new Configuration();
        /** @var array<string, array<string, mixed>|string> $config */
        $config = $this->processConfiguration($configuration, $configs);

        /** @var array<string, mixed> $connection */
        $connection = $config['connection'];
        /** @var array<string, mixed> $order */
        $order = $config['order'];
        /** @var array<string, mixed> $event */
        $event = $config['event'];
        /** @var array<string, mixed> $validation */
        $validation = $config['validation'];

        $container->setParameter('ipedis_rabbit.connection', $connection);
        $container->setParameter('ipedis_rabbit.order', $order);
        $container->setParameter('ipedis_rabbit.event', $event);
        $container->setParameter('ipedis_rabbit.validation', $validation);

        /** @var string $protocolVersion */
        $protocolVersion = $config['protocol_version'];
        /** @var string $serviceName */
        $serviceName = $config['service_name'];
        $container->setParameter('ipedis_rabbit.protocol_version', $protocolVersion);
        $container->setParameter('ipedis_rabbit.service_name', $serviceName);


        $yamlFileLoader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../Resources/config')
        );

        $yamlFileLoader->load('services.yaml');
        $this->injectTaggedWorkerService($container);
    }

    protected function injectTaggedWorkerService(ContainerBuilder $container): void
    {
        $definition = $container->findDefinition(WorkerContainer::class);
        $taggedWorkers = $container->findTaggedServiceIds('ipedis_rabbit.worker');
        foreach (array_keys($taggedWorkers) as $id) {
            $definition->addMethodCall('addWorker', [new Reference($id)]);
        }
    }

    #[\Override]
    public function getAlias(): string
    {
        return "ipedis_rabbit";
    }
}
