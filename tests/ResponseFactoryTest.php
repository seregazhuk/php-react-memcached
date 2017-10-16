<?php

namespace seregazhuk\React\Memcached\tests;

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

    /** @test */
    public function it_throws_exception_for_unknown_command()
    {
        $this->expectException(WrongCommandException::class);
        $factory = new Factory();
        $factory->makeByCommand('unknown', 'some-response');
    }
}
