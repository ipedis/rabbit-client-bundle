<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Rabbit\Service\Connectable;


use Ipedis\Bundle\Rabbit\Service\Validator\MessagePayloadValidator;
use Ipedis\Rabbit\Channel\Factory\ChannelFactory;
use Ipedis\Rabbit\Connector;

abstract class Connectable
{
    use Connector;

    private string$host;

    private int $port;

    private string $user;

    private string $password;

    private string $type;

    private string $exchangeName;

    protected array $exchangeConfig;

    public function __construct(
        array $connectionConfig,
        array $exchangeConfig,
        private ChannelFactory $channelFactory,
        private MessagePayloadValidator $messagePayloadValidator
    ) {
        $this->host = $connectionConfig['host'];
        $this->port = $connectionConfig['port'];
        $this->user = $connectionConfig['user'];
        $this->password = $connectionConfig['password'];
        $this->exchangeName = $exchangeConfig['exchange'];
        $this->type = $exchangeConfig['type'];
        $this->exchangeConfig = $exchangeConfig;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getChannelFactory(): ChannelFactory
    {
        return $this->channelFactory;
    }

    public function getMessagePayloadValidator(): MessagePayloadValidator
    {
        return $this->messagePayloadValidator;
    }

    protected function getExchangeType(): string
    {
        return $this->type;
    }

    protected function getExchangeName(): string
    {
        return $this->exchangeName;
    }
}
