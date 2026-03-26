<?php

declare(strict_types=1);

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
    public const DEV_ENV_SHORTCODE = 'dev';

    public const CHANNEL_NAME_SEPARATOR = '.';

    /**
     * Validator library
     */
    private readonly Validator $validator;

    /**
     * Location of json schema files
     */
    private readonly string $schemaBasePath;

    /**
     * Disable validation on dev mode
     */
    private readonly bool $disableOnDevMode;

    /**
     * Enable or bypass validation
     */
    private readonly bool $enableValidation;

    private readonly string $queuePrefix;

    /**
     * @param array{schema_base_path: string, disable_on_dev_mode: bool, enabled: bool} $validatorConfig
     * @param array{env?: string} $orderConfig
     */
    public function __construct(
        array $validatorConfig,
        /**
         * Current working environment
         * (dev, prod, test, etc...)
         */
        private readonly string $currentEnv,
        array $orderConfig,
        private readonly RabbitEventLogger $logger,
        private readonly JsonSchemaContainer $schemaContainer
    ) {
        $this->schemaBasePath = $validatorConfig['schema_base_path'];
        $this->disableOnDevMode = $validatorConfig['disable_on_dev_mode'];
        $this->enableValidation = $validatorConfig['enabled'];
        $this->validator = new Validator();
        $this->queuePrefix = (empty($orderConfig['env'])) ? '' : $orderConfig['env'];
    }

    /**
     * Validate message payload with json schema
     *
     * @throws MessagePayloadInvalidSchemaException
     */
    public function validate(MessagePayloadInterface $messagePayload): void
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
        $data = json_decode($messagePayload->getStringifyData());

        $error = $this->validator->schemaValidation($data, $schema);

        if (!is_null($error)) {
            $this->logger->writeError(
                sprintf(
                    '[RABBIT][MESSAGE_PAYLOAD_VALIDATOR] Invalid schema found for channel {%s}',
                    $messagePayload->getChannel()
                ),
                [
                    'error' => $error->message(),
                    'keyword' => $error->keyword(),
                    'args' => $error->args(),
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
     * @throws MessagePayloadInvalidSchemaException
     */
    private function getJsonSchemaForChannel(string $channel): Schema
    {
        /**
         * Get json file path from channel name
         */
        $jsonFilePath = $this->getJsonPath($channel);

        /**
         *  check before if schema is on schema container
         */
        if (!$this->schemaContainer->hasSchema($jsonFilePath)) {
            /**
             * Absolute location of json file
             */
            $jsonFileAbsolutePath = sprintf('%s/%s/schema.json', $this->schemaBasePath, $jsonFilePath);
            if (!file_exists($jsonFileAbsolutePath)) {
                throw new MessagePayloadInvalidSchemaException(sprintf('No schema found for channel {%s}', $channel));
            }

            /** get content of schema.json */
            $fileContent = file_get_contents($jsonFileAbsolutePath);
            if ($fileContent === false) {
                throw new MessagePayloadInvalidSchemaException(sprintf('Unable to read schema file for channel {%s}', $channel));
            }

            $jsonSchema = json_decode($fileContent, false);
            if (!is_object($jsonSchema)) {
                throw new MessagePayloadInvalidSchemaException(sprintf('Invalid JSON schema for channel {%s}', $channel));
            }

            $this->schemaContainer->addSchema($jsonFilePath, $jsonSchema);
        }

        return $this->validator->loader()->loadObjectSchema($this->schemaContainer->getSchema($jsonFilePath));
    }

    /**
     * @param array<string, mixed> $schema
     */
    #[\Deprecated(message: 'Replaced by addJsonSchema will be removed on the version 2.1')]
    public function addJsonSchemaFromArray(string $channel, array $schema): void
    {
        $json = json_encode($schema);
        if ($json === false) {
            throw new MessagePayloadInvalidSchemaException(
                sprintf('Unable to encode schema array for channel {%s}', $channel)
            );
        }

        $decoded = json_decode($json, false);
        if (!is_object($decoded)) {
            throw new MessagePayloadInvalidSchemaException(
                sprintf('Decoded JSON schema is not an object for channel {%s}', $channel)
            );
        }

        $this->addJsonSchema($channel, $decoded);
    }

    public function addJsonSchema(string $channel, object $schema): void
    {
        $this->schemaContainer->addSchema($this->getJsonPath($channel), $schema);
    }

    /**
     * Format channel to get json path
     */
    private function getJsonPath(string $channel): string
    {
        if ($this->queuePrefix !== '' && $this->queuePrefix !== '0') {
            //string to replace
            $search = ['-' . $this->queuePrefix, $this->queuePrefix . '.'];
            $channel = str_replace($search, '', $channel);
        }

        return str_replace(self::CHANNEL_NAME_SEPARATOR, DIRECTORY_SEPARATOR, $channel);
    }
}
