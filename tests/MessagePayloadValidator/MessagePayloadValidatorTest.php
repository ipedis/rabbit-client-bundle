<?php


use Ipedis\Bundle\Rabbit\Service\Validator\MessagePayloadValidator;


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

beforeEach(function () {
    $this->messageValidatorMock = $this->getMockBuilder(MessagePayloadValidator::class)
        ->disableOriginalConstructor()
        ->onlyMethods([])
        ->getMock();

    // add value for queuePrefix
    $queuePrefixClosure = function () {
        $this->queuePrefix = 'prod';
    };
    $queuePrefixClosure = $queuePrefixClosure->bindTo($this->messageValidatorMock, MessagePayloadValidator::class);
    $queuePrefixClosure();
});
