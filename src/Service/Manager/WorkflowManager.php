<?php

namespace Ipedis\Bundle\Rabbit\Service\Manager;


use Ipedis\Bundle\Rabbit\Service\Connectable\OrderConnectable;
use Ipedis\Bundle\Rabbit\Service\Validator\MessagePayloadValidator;
use Ipedis\Rabbit\Channel\Factory\ChannelFactory;

class WorkflowManager extends OrderConnectable
{
    use \Ipedis\Rabbit\Workflow\Manager;

    public function __construct(array $connectionConfig, array $exchangeConfig, ChannelFactory $channelFactory, MessagePayloadValidator $messagePayloadValidator)
    {
        parent::__construct($connectionConfig, $exchangeConfig, $channelFactory, $messagePayloadValidator);

        $this->connect();
        $this->resetOrdersQueue();
    }
}
