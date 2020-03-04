<?php

namespace Ipedis\Bundle\Rabbit\Service\Validator;


use Ipedis\Rabbit\Exception\MessagePayload\MessagePayloadInvalidSchemaException;
use Ipedis\Rabbit\MessagePayload\MessagePayloadInterface;
use Ipedis\Rabbit\MessagePayload\Validator\ValidatorInterface;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationResult;
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
    private $validator;

    /**
     * Location of json schema files
     *
     * @var string $schemaBasePath
     */
    private $schemaBasePath;

    /**
     * Disable validation on dev mode
     *
     * @var bool $disableOnDevMode
     */
    private $disableOnDevMode;

    /**
     * Current working environment
     * (dev, prod, test, etc...)
     *
     * @var string
     */
    private $currentEnv;

    public function __construct(array $validatorConfig, string $currentEnv)
    {
        $this->schemaBasePath = $validatorConfig['schema_base_path'];
        $this->disableOnDevMode = $validatorConfig['disable_on_dev_mode'];
        $this->currentEnv = $currentEnv;
        $this->validator = new Validator();
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
            $this->currentEnv === self::DEV_ENV_SHORTCODE &&
            $this->disableOnDevMode
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
        $data = json_decode($messagePayload->getStringifyData());

        /** @var ValidationResult $result */
        $result = $this->validator->schemaValidation($data, $schema);

        if (!$result->isValid()) {
            throw new MessagePayloadInvalidSchemaException(sprintf('Invalid schema found for channel {%s}', $messagePayload->getChannel()));
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
        $jsonFilePath = str_replace(self::CHANNEL_NAME_SEPARATOR, DIRECTORY_SEPARATOR, $channel);

        /**
         * Absolute location of json file
         */
        $jsonFileAbsolutePath = sprintf('%s/%s/schema.json', $this->schemaBasePath, $jsonFilePath);
        if (!file_exists($jsonFileAbsolutePath)) {
            throw new MessagePayloadInvalidSchemaException(sprintf('No schema found for channel {%s}', $channel));
        }

        return Schema::fromJsonString(file_get_contents($jsonFileAbsolutePath));
    }
}
