<?php


namespace Ipedis\Bundle\Rabbit\Service\Connectable;

use Ipedis\Bundle\Rabbit\Service\Validator\MessagePayloadValidator;
use Ipedis\Rabbit\Channel\Factory\ChannelFactory;

abstract class EventConnectable extends Connectable
{
    /**
     * Recovery Event store endpoint for error fallback
     *
     * @var string
     */
    private $recoveryEventStoreEndpoint;

    /**
     * Secret key used for signing request
     *
     * @var string
     */
    private $secretKey;

    /**
     * Hashing algorithm used for signing request
     *
     * @var string
     */
    private $hashingAlgorithm;

    /**
     * List of headers to include in signature.
     *
     * @var array
     */
    private $headersList;

    public function __construct(
        array $connectionConfig,
        array $exchangeConfig,
        ChannelFactory $channelFactory,
        MessagePayloadValidator $messagePayloadValidator
    ) {
        parent::__construct($connectionConfig, $exchangeConfig, $channelFactory, $messagePayloadValidator);

        $this->recoveryEventStoreEndpoint = $exchangeConfig['recovery_endpoint'];
        $this->secretKey = $exchangeConfig['http_signature']['secret_key'];
        $this->hashingAlgorithm = $exchangeConfig['http_signature']['algorithm'];
        $this->headersList = $exchangeConfig['http_signature']['headers'];
    }

    /**
     * Recovery Event store endpoint for error fallback
     *
     * @return string
     */
    public function getRecoveryEventStoreEndpoint(): string
    {
        return $this->recoveryEventStoreEndpoint;
    }

    /**
     * Secret key used for signing request.
     *
     * @return string
     */
    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    /**
     * alias of getSecretKey, must be rename according to http-signature library.
     * @return string
     */
    public function getSignatureKey(): string
    {
        return $this->getSecretKey();
    }

    /**
     * Hashing algorithm used for signing request.
     *
     * @return string
     */
    public function getHashingAlgorithm(): string
    {
        return $this->hashingAlgorithm;
    }

    /**
     * List of headers to include in signature.
     *
     * @return array
     */
    public function getHeadersList(): array
    {
        return $this->headersList;
    }
}
