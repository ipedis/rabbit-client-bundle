<?php


namespace Ipedis\Bundle\Rabbit\DependencyInjection;


use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class RabbitExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        var_dump($configs);
    }
}