<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Rabbit\Service\Manager;

use Ipedis\Bundle\Rabbit\Service\Connectable\OrderConnectable;
use Ipedis\Bundle\Rabbit\Service\Validator\MessagePayloadValidator;
use Ipedis\Rabbit\Channel\Factory\ChannelFactory;
use Ipedis\Rabbit\Workflow\Manager;

class WorkflowManager extends OrderConnectable
{
    use Manager;

    /**
     * @param array<string, mixed> $connectionConfig
     * @param array<string, mixed> $exchangeConfig
     */
    public function __construct(array $connectionConfig, array $exchangeConfig, ChannelFactory $channelFactory, MessagePayloadValidator $messagePayloadValidator)
    {
        parent::__construct($connectionConfig, $exchangeConfig, $channelFactory, $messagePayloadValidator);

        $this->connect();
        $this->resetOrdersQueue();
    }
}
