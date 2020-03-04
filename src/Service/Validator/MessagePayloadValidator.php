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

    public function __construct(array $validatorConfig, Validator $validator)
    {
        $this->schemaBasePath = $validatorConfig['schema_base_path'];
        $this->validator = $validator;
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
         * Load schema for channel
         */
        $schema = $this->getSchemaForChannel($messagePayload->getChannel());

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
    private function getSchemaForChannel(string $channel): Schema
    {
        $schemaFilePath = str_replace('.', DIRECTORY_SEPARATOR, $channel);
        $schemaAbsolutePath = sprintf('%s/%s/schema.json', $this->schemaBasePath, $schemaFilePath);

        if (!file_exists($schemaAbsolutePath)) {
            throw new MessagePayloadInvalidSchemaException(sprintf('No schema found for channel {%s}', $channel));
        }

        return Schema::fromJsonString(file_get_contents($schemaAbsolutePath));
    }
}
