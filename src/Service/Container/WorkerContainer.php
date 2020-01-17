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
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name)
    {
        return (!empty($this->workers[$name]));
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->workers[$name];
    }
}
