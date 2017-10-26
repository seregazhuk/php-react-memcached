<?php

namespace seregazhuk\React\Memcached\tests;

use PHPUnit\Framework\TestCase;
use seregazhuk\React\Memcached\Exception\WrongCommandException;
use seregazhuk\React\Memcached\Protocol\Request\Factory;
use seregazhuk\React\Memcached\Protocol\Request\Request;
use seregazhuk\React\Memcached\Protocol\Request\SimpleRequest;
use seregazhuk\React\Memcached\Protocol\Request\StorageRequest;

class RequestFactoryTest extends TestCase
{
    /** @test */
    public function it_returns_instance_of_request()
    {
        $factory = new Factory();
        $this->assertInstanceOf(Request::class, $factory->create('version', []));
    }

    /** @test */
    public function it_creates_simple_request_for_non_storage_commands()
    {
        $factory = new Factory();
        $this->assertInstanceOf(SimpleRequest::class, $factory->create('version', []));
    }

    /** @test */
    public function it_creates_storage_request_for_storage_commands()
    {
        $factory = new Factory();
        $this->assertInstanceOf(StorageRequest::class, $factory->create('set', ['key', 'val']));
    }

    /** @test */
    public function it_throws_exception_for_unknown_command()
    {
        $this->expectException(WrongCommandException::class);
        $factory = new Factory();
        $factory->create('unknown', []);
    }
}
