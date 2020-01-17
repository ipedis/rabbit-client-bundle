<?php


namespace Ipedis\Bundle\Rabbit\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

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
    }

    public function getAlias()
    {
        return "ipedis_rabbit";
    }
}