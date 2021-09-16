<?php

namespace Ipedis\Bundle\Rabbit\Service\Validator;


use Ipedis\Bundle\Rabbit\Service\Logger\RabbitEventLogger;
use Ipedis\Rabbit\Exception\MessagePayload\MessagePayloadInvalidSchemaException;
use Ipedis\Rabbit\MessagePayload\MessagePayloadInterface;
use Ipedis\Rabbit\MessagePayload\Validator\ValidatorInterface;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;
/**
 * Default Validator for validating rabbitMQ message payload
 * against predefined json schema
 *
 * Class MessagePayloadValidator
 * @package Ipedis\Bundle\Rabbit\Service\Validator
 */
class MessagePayloadValidator implements ValidatorInterface
{
    const DEV_ENV_SHORTCODE = 'dev';
    const CHANNEL_NAME_SEPARATOR = '.';

    /**
     * Validator library
     *
     * @var Validator $validator
     */
    private Validator $validator;

    /**
     * Location of json schema files
     *
     * @var string $schemaBasePath
     */
    private string $schemaBasePath;

    /**
     * Disable validation on dev mode
     *
     * @var bool $disableOnDevMode
     */
    private bool $disableOnDevMode;

    /**
     * Enable or bypass validation
     * @var bool
     */
    private bool $enableValidation;

    /**
     * Current working environment
     * (dev, prod, test, etc...)
     *
     * @var string
     */
    private string $currentEnv;
    /**
     * @var string
     */
    private string $queuePrefix;
    /**
     * @var RabbitEventLogger
     */
    private RabbitEventLogger $logger;

    public function __construct(
        array $validatorConfig,
        string $currentEnv,
        array $orderConfig,
        RabbitEventLogger $logger
    ) {
        $this->schemaBasePath = $validatorConfig['schema_base_path'];
        $this->disableOnDevMode = $validatorConfig['disable_on_dev_mode'];
        $this->enableValidation = $validatorConfig['enabled'];
        $this->currentEnv = $currentEnv;
        $this->validator = new Validator();
        $this->queuePrefix = (empty($orderConfig['env'])) ? '' : $orderConfig['env'];
        $this->logger = $logger;
    }

    /**
     * Validate message payload with json schema
     *
     * @param MessagePayloadInterface $messagePayload
     * @throws MessagePayloadInvalidSchemaException
     */
    public function validate(MessagePayloadInterface $messagePayload)
    {
        /**
         * Disable validation of message payload if
         * - on dev mode and
         * - setting is activated
         */
        if (
            ($this->currentEnv === self::DEV_ENV_SHORTCODE &&
            $this->disableOnDevMode) || !$this->enableValidation
        ) {
            return;
        }

        /**
         * Load schema for channel
         */
        $schema = $this->getJsonSchemaForChannel($messagePayload->getChannel());

        /**
         * Transform data to object
         */
        $data = $messagePayload->getData();

        $result = $this->validator->schemaValidation($data, $schema);

        if (!$result->isValid()) {
            $this->logger->writeError(
                sprintf(
                    '[RABBIT][MESSAGE_PAYLOAD_VALIDATOR] Invalid schema found for channel {%s}',
                    $messagePayload->getChannel()
                ),
                [
                    'error' => $result->getErrors()
                ]
            );
            throw new MessagePayloadInvalidSchemaException(
                sprintf('Invalid schema found for channel {%s}', $messagePayload->getChannel())
            );
        }
    }

    /**
     * Find and load json schema for channel
     *
     * @param string $channel
     * @return Schema
     * @throws MessagePayloadInvalidSchemaException
     */
    private function getJsonSchemaForChannel(string $channel): Schema
    {
        /**
         * Get json file path from channel name
         */
        $jsonFilePath = $this->getJsonPath($channel);

        /**
         * Absolute location of json file
         */
        $jsonFileAbsolutePath = sprintf('%s/%s/schema.json', $this->schemaBasePath, $jsonFilePath);
        if (!file_exists($jsonFileAbsolutePath)) {
            throw new MessagePayloadInvalidSchemaException(sprintf('No schema found for channel {%s}', $channel));
        }

        return Schema::fromJsonString(file_get_contents($jsonFileAbsolutePath));
    }

    /**
     * Format channel to get json path
     * @param string $channel
     * @return string
     */
    private function getJsonPath(string $channel): string
    {
        if (!empty($this->queuePrefix)) {
            //string to replace
            $search = ['-' . $this->queuePrefix, $this->queuePrefix . '.'];
            $channel = str_replace($search, '', $channel);
        }
        return str_replace(self::CHANNEL_NAME_SEPARATOR, DIRECTORY_SEPARATOR, $channel);
    }
}
