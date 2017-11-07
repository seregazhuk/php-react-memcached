<?php

namespace seregazhuk\React\Memcached\tests\Unit;

use Mockery;
use Mockery\MockInterface;
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

    protected function expectPromiseRejectsWith($promise, $reason)
    {
        $this->assertInstanceOf(PromiseInterface::class, $promise);

        /** @var PromiseInterface $promise */
        $promise->then(null, function($error) {
            $this->assertNull($error);
            $this->fail('promise resolved');
        });
        $promise->then($this->expectCallableNever(), $this->expectCallableOnceWithArgumentOfType($reason));
        return $promise;
    }

    /**
     * @param array $parameters
     * @return MockInterface|callable
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

    /**
     * @param string $type
     * @return MockInterface|callable
     */
    protected function expectCallableOnceWithArgumentOfType($type)
    {
        $mock = Mockery::mock(CallableStub::class);

        $mock->shouldReceive('__invoke')
            ->with(Mockery::type($type))
            ->once();

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
