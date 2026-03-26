<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Rabbit\Service\Container;

class WorkerContainer
{
    /** @var array<string, mixed> */
    protected array $workers = [];

    /**
     * @param array<string, mixed> $workers
     */
    public function __construct(array $workers)
    {
        foreach ($workers as $id => $worker) {
            $this->workers[$id] = $worker;
        }
    }

    public function has(string $name): bool
    {
        return (!empty($this->workers[$name]));
    }

    public function get(string $name): mixed
    {
        return $this->workers[$name];
    }
}
