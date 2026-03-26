<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Rabbit\Service\Connectable;

use Ipedis\Bundle\Rabbit\Service\Validator\MessagePayloadValidator;
use Ipedis\Rabbit\Channel\Factory\ChannelFactory;

abstract class EventConnectable extends Connectable
{
    private readonly string $recoveryEventStoreEndpoint;

    private readonly string $secretKey;

    private readonly string $hashingAlgorithm;

    /** @var array<int, string> */
    private readonly array $headersList;

    /**
     * @param array<string, mixed> $connectionConfig
     * @param array<string, mixed> $exchangeConfig
     */
    public function __construct(
        array $connectionConfig,
        array $exchangeConfig,
        ChannelFactory $channelFactory,
        MessagePayloadValidator $messagePayloadValidator
    ) {
        parent::__construct($connectionConfig, $exchangeConfig, $channelFactory, $messagePayloadValidator);

        /** @var string $recoveryEndpoint */
        $recoveryEndpoint = $exchangeConfig['recovery_endpoint'];
        $this->recoveryEventStoreEndpoint = $recoveryEndpoint;
        /** @var array{secret_key: string, algorithm: string, headers: array<int, string>} $httpSignature */
        $httpSignature = $exchangeConfig['http_signature'];
        $this->secretKey = $httpSignature['secret_key'];
        $this->hashingAlgorithm = $httpSignature['algorithm'];
        $this->headersList = $httpSignature['headers'];
    }

    /**
     * Recovery Event store endpoint for error fallback
     */
    public function getRecoveryEventStoreEndpoint(): string
    {
        return $this->recoveryEventStoreEndpoint;
    }

    /**
     * Secret key used for signing request.
     */
    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    /**
     * alias of getSecretKey, must be rename according to http-signature library.
     */
    public function getSignatureKey(): string
    {
        return $this->getSecretKey();
    }

    /**
     * Hashing algorithm used for signing request.
     */
    public function getHashingAlgorithm(): string
    {
        return $this->hashingAlgorithm;
    }

    /**
     * List of headers to include in signature.
     */
    /**
     * @return array<int, string>
     */
    public function getHeadersList(): array
    {
        return $this->headersList;
    }
}
