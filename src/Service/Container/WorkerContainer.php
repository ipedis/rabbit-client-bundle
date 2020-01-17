<?php


namespace Ipedis\Bundle\Rabbit\Service\Container;


class WorkerContainer
{
    protected $workers = [];

    protected function addWorker($worker)
    {
        $this->workers[] = $worker;
    }
}