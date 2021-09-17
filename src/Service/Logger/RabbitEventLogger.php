<?php


namespace Ipedis\Bundle\Rabbit\Service\Logger;


use Psr\Log\LoggerInterface;

class RabbitEventLogger
{

    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function writeError(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function writeInfo(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function writeDebug(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public function writeLog(string $level, string $message, array $context = [])
    {
        $this->logger->log($level, $message, $context);
    }
}
