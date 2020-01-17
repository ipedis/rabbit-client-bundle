<?php


namespace Ipedis\Bundle\Rabbit\DependencyInjection;


use Ipedis\Bundle\Rabbit\Service\Container\WorkerContainer;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class RabbitExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('ipedis_rabbit.connection', $config['connection']);
        $container->setParameter('ipedis_rabbit.order', $config['order']);
        $container->setParameter('ipedis_rabbit.event', $config['event']);


        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../Resources/config')
        );

        $loader->load('services.yaml');
        $this->injectTaggedWorkerService($container);
    }

    protected function injectTaggedWorkerService(ContainerBuilder $container)
    {
        $definition = $container->findDefinition(WorkerContainer::class);
        $taggedWorkers = $container->findTaggedServiceIds('ipedis_rabbit.worker');
        foreach ($taggedWorkers as $id => $tags) {
            $definition->addMethodCall('addWorker', [new Reference($id)]);
        }
    }

    public function getAlias()
    {
        return "ipedis_rabbit";
    }
}