<?php


use Ipedis\Bundle\Rabbit\Service\Validator\JsonSchemaContainer;
use Ipedis\Bundle\Rabbit\Service\Validator\MessagePayloadValidator;
use Ipedis\Rabbit\Exception\MessagePayload\MessagePayloadInvalidSchemaException;
use Opis\JsonSchema\Schema;


it('should remove queue prefix', function () {
    $that = $this;
    $removePrefixAssertClosure = function () use ($that) {
        $that->assertEquals(
            'v1/service/aggregate/something',
            $that->messageValidatorMock->getJsonPath('prod.v1.service.aggregate.something')
        );
        $that->assertEquals(
            'v1/service/aggregate/something',
            $that->messageValidatorMock->getJsonPath('prod.v1-prod.service.aggregate.something')
        );
        $that->assertEquals(
            'v1/service/aggregate/something',
            $that->messageValidatorMock->getJsonPath('v1-prod.service.aggregate.something')
        );
        $that->assertEquals(
            'v1/service/aggregate/something',
            $that->messageValidatorMock->getJsonPath('v1.service.aggregate.something')
        );
    };

    $doRemovePrefixAssert = $removePrefixAssertClosure->bindTo(
        $this->messageValidatorMock,
        MessagePayloadValidator::class
    );

    $doRemovePrefixAssert();
});

it('should add data on schemaContainer', function () {
    $this->messageValidatorMock->addJsonSchemaFromArray('v1/service/aggregate/something',
        [
            'test' => 'json'
        ]
    );

    $that = $this;
    $checkSchemaContainerClosure = function () use ($that) {
        expect($that->messageValidatorMock->schemaContainer->hasSchema('v1/service/aggregate/something'))->toBeTrue();
    };

    $doCheckSchemaContainerClosure = $checkSchemaContainerClosure->bindTo(
        $this->messageValidatorMock,
        MessagePayloadValidator::class
    );

    $doCheckSchemaContainerClosure();
});

it('must return Schema', function () {
    $this->messageValidatorMock->addJsonSchemaFromArray('v1/service/aggregate/something',
        [
            'test' => 'json'
        ]
    );

    $that = $this;
    $getJsonSchemaForChannelClosure = function () use ($that) {
        $that->assertInstanceOf(
            Schema::class,
            $that->messageValidatorMock->getJsonSchemaForChannel('prod.v1.service.aggregate.something')
        );
    };

    $doGetJsonSchemaForChannelClosure = $getJsonSchemaForChannelClosure->bindTo(
        $this->messageValidatorMock,
        MessagePayloadValidator::class
    );

    $doGetJsonSchemaForChannelClosure();
});

it('must throw an exception when no schema provided on schema container and json path', function () {
    $that = $this;
    $getJsonSchemaForChannelClosure = function () use ($that) {
        $that->expectException(MessagePayloadInvalidSchemaException::class);
        $that->messageValidatorMock->getJsonSchemaForChannel('prod.v1.service.aggregate.something');
    };

    $doGetJsonSchemaForChannelClosure = $getJsonSchemaForChannelClosure->bindTo(
        $this->messageValidatorMock,
        MessagePayloadValidator::class
    );

    $doGetJsonSchemaForChannelClosure();
});

beforeEach(function () {
    $this->messageValidatorMock = $this->getMockBuilder(MessagePayloadValidator::class)
        ->disableOriginalConstructor()
        ->onlyMethods([])
        ->getMock();

    // add value for queuePrefix
    $queuePrefixClosure = function () {
        $this->queuePrefix = 'prod';
        $this->schemaBasePath = 'test/schema';
        $this->schemaContainer = new JsonSchemaContainer();
    };
    $queuePrefixClosure = $queuePrefixClosure->bindTo($this->messageValidatorMock, MessagePayloadValidator::class);
    $queuePrefixClosure();
});
