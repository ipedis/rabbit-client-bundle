<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Rabbit\Service\Connectable;

abstract class OrderConnectable extends Connectable
{
    public function getQueuePrefix(): string
    {
        /** @var string $env */
        $env = $this->exchangeConfig['env'] ?? '';

        return $env;
    }
}
