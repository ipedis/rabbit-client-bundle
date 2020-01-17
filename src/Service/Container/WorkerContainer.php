<?php


namespace Ipedis\Bundle\Rabbit\Service\Container;


class WorkerContainer
{
    protected $workers = [];

    public function __construct($workers)
    {
        foreach ($workers as $id => $worker)
        {
            $this->workers[$id] = $worker;
            dump($this->workers);
        }
    }

    /**
     * @param string $name
     */
    public function has(string $name)
    {

    }
}
