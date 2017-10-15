<?php

namespace tests;

use Mockery;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use React\Promise\PromiseInterface;

class TestCase extends PhpUnitTestCase
{
    protected function expectPromiseResolve($promise)
    {
        $this->assertInstanceOf(PromiseInterface::class, $promise);

        /** @var PromiseInterface $promise */
        $promise->then(null, function($error) {
            $this->assertNull($error);
            $this->fail('promise rejected');
        });
        $promise->then($this->expectCallableOnce(), $this->expectCallableNever());
        return $promise;
    }

    protected function expectCallableOnce()
    {
        $spy = Mockery::spy();
        $spy->shouldHaveReceived('__invoke')->once();
    }

    protected function expectCallableNever()
    {
        $spy = Mockery::spy();
        $spy->shouldNotHaveReceived('__invoke');
    }

}
