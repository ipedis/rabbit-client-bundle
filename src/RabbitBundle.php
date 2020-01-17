<?php


namespace Ipedis\Bundle\Rabbit;


use Ipedis\Bundle\Rabbit\DependencyInjection\RabbitExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RabbitBundle extends Bundle
{
    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new RabbitExtension();
        }
        return $this->extension;
    }
}