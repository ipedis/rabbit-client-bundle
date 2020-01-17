<?php


namespace Ipedis\Bundle\Rabbit\Service;


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
    private $exchange;
    /**
     * @var string
     */
    private $type;


    public function __construct(array $connectionConfig, array $exchangeConfig)
    {
        $this->host = $connectionConfig['host'];
        $this->port = $connectionConfig['port'];
        $this->user = $connectionConfig['user'];
        $this->password = $connectionConfig['password'];
        $this->exchange = $exchangeConfig['exchange'];
        $this->type = $exchangeConfig['type'];
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

    protected function getExchangeType(): string
    {
        return $this->type;
    }

    protected function getExchangeName(): string
    {
        return $this->exchange;
    }
}