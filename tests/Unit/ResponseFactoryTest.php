<?php

namespace seregazhuk\React\Memcached\tests\Unit;

use PHPUnit\Framework\TestCase;
use seregazhuk\React\Memcached\Exception\WrongCommandException;
use seregazhuk\React\Memcached\Protocol\Response\Factory;
use seregazhuk\React\Memcached\Protocol\Response\Response;

class ResponseFactoryTest extends TestCase
{
    /** @test */
    public function it_returns_instance_of_response()
    {
        $factory = new Factory();
        $this->assertInstanceOf(Response::class, $factory->makeByCommand('version', 'version 123'));
    }
}
