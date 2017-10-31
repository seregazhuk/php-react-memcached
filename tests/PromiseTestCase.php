<?php

namespace seregazhuk\React\Memcached\tests;

use Mockery;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use React\Promise\PromiseInterface;

abstract class PromiseTestCase extends PhpUnitTestCase
{
    protected function expectPromiseResolvesWith($promise, $value)
    {
        $this->assertInstanceOf(PromiseInterface::class, $promise);

        /** @var PromiseInterface $promise */
        $promise->then(null, function($error) {
            $this->assertNull($error);
            $this->fail('promise rejected');
        });
        $promise->then($this->expectCallableOnce([$value]), $this->expectCallableNever());
        return $promise;
    }

    protected function expectPromiseRejects($promise)
    {
        $this->assertInstanceOf(PromiseInterface::class, $promise);

        /** @var PromiseInterface $promise */
        $promise->then(null, function($error) {
            $this->assertNull($error);
            $this->fail('promise resolved');
        });
        $promise->then($this->expectCallableNever(), $this->expectCallableOnce());
        return $promise;
    }

    /**
     * @param array $parameters
     * @return Mockery\MockInterface|callable
     */
    protected function expectCallableOnce(array $parameters = [])
    {
        $mock = Mockery::mock(CallableStub::class);

        if ($parameters) {
            $mock->shouldReceive('__invoke')->withArgs($parameters)->once();
        } else {
            $mock->shouldReceive('__invoke')->once();
        }

        return $mock;
    }

    protected function expectCallableNever()
    {
        $mock = Mockery::mock(CallableStub::class);
        $mock->shouldNotReceive('__invoke');
        return $mock;
    }

    protected function tearDown()
    {
        parent::tearDown();
        if ($container = Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }

        Mockery::close();
    }
}