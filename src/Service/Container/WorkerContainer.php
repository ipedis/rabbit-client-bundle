<?php

declare(strict_types=1);

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

    public function has(string $name): bool
    {
        return (!empty($this->workers[$name]));
    }

    /**
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->workers[$name];
    }
}
