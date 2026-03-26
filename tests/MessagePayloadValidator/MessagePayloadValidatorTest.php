<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Rabbit\Tests\MessagePayloadValidator;

use Ipedis\Bundle\Rabbit\Service\Logger\RabbitEventLogger;
use Ipedis\Bundle\Rabbit\Service\Validator\JsonSchemaContainer;
use Ipedis\Bundle\Rabbit\Service\Validator\MessagePayloadValidator;
use Ipedis\Rabbit\Exception\MessagePayload\MessagePayloadInvalidSchemaException;
use Ipedis\Rabbit\MessagePayload\EventMessagePayload;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use ReflectionMethod;
use ReflectionProperty;

final class MessagePayloadValidatorTest extends TestCase
{
    private MessagePayloadValidator $messageValidator;

    private MessagePayloadValidator $messageValidatorMock;

    protected function setUp(): void
    {
        $this->messageValidator = new MessagePayloadValidator(
            ['schema_base_path' => 'tests/schemas', 'disable_on_dev_mode' => false, 'enabled' => true],
            'dummy',
            ['env' => 'dummy'],
            new RabbitEventLogger(new NullLogger()),
            new JsonSchemaContainer()
        );

        $this->messageValidatorMock = $this
            ->getMockBuilder(MessagePayloadValidator::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->setPrivateProperty($this->messageValidatorMock, 'queuePrefix', 'dummy');
        $this->setPrivateProperty($this->messageValidatorMock, 'schemaBasePath', 'tests/schemas');
        $this->setPrivateProperty($this->messageValidatorMock, 'schemaContainer', new JsonSchemaContainer());
        $this->setPrivateProperty($this->messageValidatorMock, 'validator', new Validator());
    }

    #[DataProvider('queuePrefixProvider')]
    #[Test]
    public function should_remove_queue_prefix(string $expectedPath, string $channel): void
    {
        $result = $this->callPrivateMethod($this->messageValidatorMock, 'getJsonPath', [$channel]);
        $this->assertSame($expectedPath, $result);
    }

    /**
     * @return \Iterator<string, array{string, string}>
     */
    public static function queuePrefixProvider(): \Iterator
    {
        yield 'simple prefix' => ['v1/service/aggregate/something', 'dummy.v1.service.aggregate.something'];
        yield 'double prefix' => ['v1/service/aggregate/something', 'dummy.v1-dummy.service.aggregate.something'];
        yield 'prefix with dash' => ['v1/service/aggregate/something', 'v1-dummy.service.aggregate.something'];
        yield 'no prefix' => ['v1/service/aggregate/something', 'v1.service.aggregate.something'];
    }

    #[Test]
    public function should_add_data_on_schema_container(): void
    {
        $this->messageValidatorMock->addJsonSchema('v1/service/aggregate/something', (object) ['test' => 'json']);

        $schemaContainer = $this->getPrivateProperty($this->messageValidatorMock, 'schemaContainer');
        $this->assertInstanceOf(JsonSchemaContainer::class, $schemaContainer);
        $this->assertTrue($schemaContainer->hasSchema('v1/service/aggregate/something'));
    }

    #[Test]
    public function must_return_schema(): void
    {
        $this->messageValidatorMock->addJsonSchema('v1/service/aggregate/something', (object) ['test' => 'json']);

        $result = $this->callPrivateMethod(
            $this->messageValidatorMock,
            'getJsonSchemaForChannel',
            ['dummy.v1.service.aggregate.something']
        );
        $this->assertInstanceOf(Schema::class, $result);
    }

    #[Test]
    public function must_throw_exception_when_no_schema_provided(): void
    {
        $this->expectException(MessagePayloadInvalidSchemaException::class);

        $this->callPrivateMethod(
            $this->messageValidatorMock,
            'getJsonSchemaForChannel',
            ['dummy.v1.service.aggregate.not-existing']
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    #[DataProvider('validPayloadProvider')]
    #[Test]
    public function must_validate_from_filesystem(string $channel, array $payload): void
    {
        $event = EventMessagePayload::build($channel, $payload);
        $this->messageValidator->validate($event);
        $this->addToAssertionCount(1);
    }

    /**
     * @return \Iterator<string, array{string, array<string, mixed>}>
     */
    public static function validPayloadProvider(): \Iterator
    {
        yield 'with prefix' => ['dummy.v1.service.aggregate.something', ['hasToFail' => true, 'name' => 'foo']];
        yield 'without prefix' => ['v1.service.aggregate.another', ['first' => 'john', 'last' => 'do']];
        yield 'no prefix something' => ['v1.service.aggregate.something', ['hasToFail' => true, 'name' => 'foo']];
        yield 'double prefix another' => ['dummy.v1-dummy.service.aggregate.another', ['first' => 'john', 'last' => 'do']];
    }

    /**
     * @param array<string, mixed> $payload
     */
    #[DataProvider('invalidPayloadProvider')]
    #[Test]
    public function must_throw_exception_when_not_valid(string $channel, array $payload): void
    {
        $this->expectException(MessagePayloadInvalidSchemaException::class);

        $event = EventMessagePayload::build($channel, $payload);
        $this->messageValidator->validate($event);
    }

    /**
     * @return \Iterator<string, array{string, array<string, mixed>}>
     */
    public static function invalidPayloadProvider(): \Iterator
    {
        yield 'string instead of bool' => ['dummy.v1.service.aggregate.something', ['hasToFail' => 'true', 'name' => 'foo']];
        yield 'null instead of string' => ['v1.service.aggregate.another', ['first' => 'john', 'last' => null]];
        yield 'extra property' => ['v1.service.aggregate.something', ['extra' => '', 'hasToFail' => true, 'name' => 'foo']];
        yield 'missing property' => ['dummy.v1-dummy.service.aggregate.another', ['first' => 'john']];
    }

    private function setPrivateProperty(object $object, string $property, mixed $value): void
    {
        $reflectionProperty = new ReflectionProperty(MessagePayloadValidator::class, $property);
        $reflectionProperty->setValue($object, $value);
    }

    private function getPrivateProperty(object $object, string $property): mixed
    {
        $reflectionProperty = new ReflectionProperty(MessagePayloadValidator::class, $property);

        return $reflectionProperty->getValue($object);
    }

    /**
     * @param array<int, mixed> $args
     */
    private function callPrivateMethod(object $object, string $method, array $args = []): mixed
    {
        $reflectionMethod = new ReflectionMethod(MessagePayloadValidator::class, $method);

        return $reflectionMethod->invoke($object, ...$args);
    }
}
