<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Rabbit;

use Ipedis\Bundle\Rabbit\DependencyInjection\RabbitExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RabbitBundle extends Bundle
{
    #[\Override]
    public function getContainerExtension(): ?ExtensionInterface
    {
        if ($this->extension === null) {
            $this->extension = new RabbitExtension();
        }

        return $this->extension ?: null;
    }
}
