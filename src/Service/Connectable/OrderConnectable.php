<?php


namespace Ipedis\Bundle\Rabbit\Service\Connectable;

abstract class OrderConnectable extends Connectable
{
    public function getQueuePrefix(): string
    {
        return (empty($orderConfig['env'])) ? '' : $orderConfig['env'];
    }
}
