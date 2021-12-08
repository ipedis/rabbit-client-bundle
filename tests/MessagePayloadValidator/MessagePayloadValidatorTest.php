<?php

use Ipedis\Bundle\Rabbit\Service\Logger\RabbitEventLogger;
use Ipedis\Bundle\Rabbit\Service\Validator\JsonSchemaContainer;
use Ipedis\Bundle\Rabbit\Service\Validator\MessagePayloadValidator;
use Ipedis\Rabbit\Exception\MessagePayload\MessagePayloadInvalidSchemaException;
use Ipedis\Rabbit\MessagePayload\EventMessagePayload;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;
use Psr\Log\NullLogger;

it('should remove queue prefix', function (string $expectedPath, string $channel) {
    $that = $this;
    $removePrefixAssertClosure = function () use ($that, $expectedPath, $channel) {
       expect($that->messageValidatorMock->getJsonPath($channel))->toBe($expectedPath);
    };

    $doRemovePrefixAssert = $removePrefixAssertClosure->bindTo(
        $this->messageValidatorMock,
        MessagePayloadValidator::class
    );

    $doRemovePrefixAssert();
})->with([
    ['v1/service/aggregate/something','dummy.v1.service.aggregate.something'],
    ['v1/service/aggregate/something','dummy.v1-dummy.service.aggregate.something'],
    ['v1/service/aggregate/something','v1-dummy.service.aggregate.something'],
    ['v1/service/aggregate/something','v1.service.aggregate.something'],
]);

it('should add data on schemaContainer', function () {
    $this->messageValidatorMock->addJsonSchemaFromArray('v1/service/aggregate/something',['test' => 'json']);
    $that = $this;
    $checkSchemaContainerClosure = function () use ($that) {
        expect($that->messageValidatorMock->schemaContainer->hasSchema('v1/service/aggregate/something'))->toBeTrue();
    };

    $doCheckSchemaContainerClosure = $checkSchemaContainerClosure->bindTo($this->messageValidatorMock, MessagePayloadValidator::class);
    $doCheckSchemaContainerClosure();
});

it('must return Schema', function () {
    $this->messageValidatorMock->addJsonSchemaFromArray('v1/service/aggregate/something', ['test' => 'json']);

    $that = $this;
    $getJsonSchemaForChannelClosure = function () use ($that) {
        $that->assertInstanceOf(
            Schema::class,
            $that->messageValidatorMock->getJsonSchemaForChannel('dummy.v1.service.aggregate.something')
        );
    };

    $doGetJsonSchemaForChannelClosure = $getJsonSchemaForChannelClosure
        ->bindTo($this->messageValidatorMock, MessagePayloadValidator::class);

    $doGetJsonSchemaForChannelClosure();
});

it('must throw an exception when no schema provided on schema container and json path', function () {
    $that = $this;
    $getJsonSchemaForChannelClosure = function () use ($that) {
        $that->expectException(MessagePayloadInvalidSchemaException::class);
        $that->messageValidatorMock->getJsonSchemaForChannel('dummy.v1.service.aggregate.not-existing');
    };

    $doGetJsonSchemaForChannelClosure = $getJsonSchemaForChannelClosure
        ->bindTo($this->messageValidatorMock, MessagePayloadValidator::class);

    $doGetJsonSchemaForChannelClosure();
});


it('must validate from issue from filesystem', function(string $channel, array $payload) {
    /** @var MessagePayloadValidator $messageValidator */
    $messageValidator = $this->messageValidator;
    $event = EventMessagePayload::build($channel, $payload);
    expect($messageValidator->validate($event))
        ->not()
        ->toThrow(MessagePayloadInvalidSchemaException::class)
    ;
})->with([
    ['dummy.v1.service.aggregate.something', ['hasToFail' => true, 'name' => 'foo']],
    ['v1.service.aggregate.another', ['first' => 'john', 'last' => 'do']],
    ['v1.service.aggregate.something', ['hasToFail' => true, 'name' => 'foo']],
    ['dummy.v1-dummy.service.aggregate.another', ['first' => 'john', 'last' => 'do']],
]);

it('must throw exception when it is not valid', function(string $channel, array $payload) {
    /** @var MessagePayloadValidator $messageValidator */
    $messageValidator = $this->messageValidator;
    $event = EventMessagePayload::build($channel, $payload);
    $messageValidator->validate($event);
})
->throws(MessagePayloadInvalidSchemaException::class)
->with([
    ['dummy.v1.service.aggregate.something', ['hasToFail' => 'true', 'name' => 'foo']],
    ['v1.service.aggregate.another', ['first' => 'john', 'last' => null]],
    ['v1.service.aggregate.something', ['extra' => '', 'hasToFail' => true, 'name' => 'foo']],
    ['dummy.v1-dummy.service.aggregate.another', ['first' => 'john']],
]);

beforeEach(function () {
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
        ->getMock()
    ;

    // add value for queuePrefix
    $queuePrefixClosure = function () {
        $this->queuePrefix = 'dummy';
        $this->schemaBasePath = 'tests/schemas';
        $this->schemaContainer = new JsonSchemaContainer();
        $this->validator = new Validator();
    };

    $queuePrefixClosure = $queuePrefixClosure->bindTo($this->messageValidatorMock, MessagePayloadValidator::class);
    $queuePrefixClosure();
});
