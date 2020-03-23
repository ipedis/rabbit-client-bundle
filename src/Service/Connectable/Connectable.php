<?php


namespace Ipedis\Bundle\Rabbit\Service\Connectable;


use Ipedis\Bundle\Rabbit\Service\Validator\MessagePayloadValidator;
use Ipedis\Rabbit\Channel\Factory\ChannelFactory;
use Ipedis\Rabbit\Connector;

abstract class Connectable
{
    use Connector;

    /**
     * @var string
     */
    private $host;
    /**
     * @var int
     */
    private $port;
    /**
     * @var string
     */
    private $user;
    /**
     * @var string
     */
    private $password;
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $exchangeName;

    /**
     * @var ChannelFactory
     */
    private $channelFactory;

    /**
     * @var MessagePayloadValidator
     */
    private $messagePayloadValidator;

    public function __construct(
        array $connectionConfig,
        array $exchangeConfig,
        ChannelFactory $channelFactory,
        MessagePayloadValidator $messagePayloadValidator
    ) {
        $this->host = $connectionConfig['host'];
        $this->port = $connectionConfig['port'];
        $this->user = $connectionConfig['user'];
        $this->password = $connectionConfig['password'];
        $this->exchangeName = $exchangeConfig['exchange'];
        $this->type = $exchangeConfig['type'];
        $this->channelFactory = $channelFactory;
        $this->messagePayloadValidator = $messagePayloadValidator;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return ChannelFactory
     */
    public function getChannelFactory(): ChannelFactory
    {
        return $this->channelFactory;
    }

    /**
     * @return MessagePayloadValidator
     */
    public function getMessagePayloadValidator(): MessagePayloadValidator
    {
        return $this->messagePayloadValidator;
    }

    /**
     * @return string
     */
    protected function getExchangeType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    protected function getExchangeName(): string
    {
        return $this->exchangeName;
    }
}
