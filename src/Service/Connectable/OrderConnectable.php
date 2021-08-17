<?php


namespace Ipedis\Bundle\Rabbit\Service\Connectable;

abstract class OrderConnectable extends Connectable
{
    public function getQueuePrefix(): string
    {
        return (empty($this->exchangeConfig['env'])) ? '' : $this->exchangeConfig['env'];
    }
}
