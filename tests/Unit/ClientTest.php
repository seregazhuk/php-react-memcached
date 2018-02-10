<?php

namespace seregazhuk\React\Memcached\tests\Unit;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use seregazhuk\React\Memcached\Client;
use seregazhuk\React\Memcached\Connection;
use seregazhuk\React\Memcached\Exception\ConnectionClosedException;
use seregazhuk\React\Memcached\Exception\Exception;
use seregazhuk\React\Memcached\Exception\WrongCommandException;
use seregazhuk\React\Memcached\Protocol\Parser;
use seregazhuk\React\PromiseTesting\TestCase;

class ClientTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Connection|MockInterface
     */
    protected $connection;

    protected function setUp()
    {
        $this->connection = Mockery::mock(Connection::class)->shouldReceive('on')->getMock();
        $this->client = new Client($this->connection, new Parser());

        parent::setUp();
    }

    /** @test */
    public function it_sends_data_to_the_connection()
    {
        $this->connection->shouldReceive('write')->with('version' . Parser::COMMAND_SEPARATOR)->once();
        $this->client->version();
    }

    /** @test */
    public function it_rejects_a_promise_when_unsupported_command_is_called()
    {
        $promise = $this->client->not_valid();
        $this->assertPromiseRejectsWith($promise, WrongCommandException::class);
    }

    /** @test */
    public function it_resolves_a_promise_with_data_from_response()
    {
        $this->connection->shouldReceive('write')->once();
        $promise = $this->client->version();

        $this->client->resolveRequests(['12345']);

        $this->assertPromiseFulfillsWith($promise, '12345');
    }

    /** @test */
    public function it_throws_exception_when_handling_response_data_without_pending_requests()
    {
        $this->expectException(Exception::class);
        $this->client->resolveRequests(['not_valid']);
    }

    /** @test */
    public function it_rejects_pending_request_when_closing()
    {
        $this->connection->shouldReceive('write')->once();
        $this->connection->shouldReceive('close')->once();
        $promise = $this->client->version();

        $this->client->close();

        $this->assertPromiseRejectsWith($promise, ConnectionClosedException::class);
    }

    /** @test */
    public function it_rejects_all_new_requests_when_closed()
    {
        $this->connection->shouldReceive('close')->once();

        $this->client->close();
        $promise = $this->client->version();
        $this->assertPromiseRejectsWith($promise, ConnectionClosedException::class);
    }

    /** @test */
    public function it_rejects_all_new_requests_when_ending()
    {
        $this->connection->shouldReceive('close')->once();

        $this->client->end();
        $promise = $this->client->version();
        $this->assertPromiseRejectsWith($promise, ConnectionClosedException::class);
    }

	/** @test */
	public function it_emits_close_event_when_closing()
	{
	    $callbackWasCalled = false;
		$this->connection->shouldReceive('close')->once();

		$this->client->on('close', function() use (&$callbackWasCalled) {
		    $callbackWasCalled = true;
        });
		$this->client->end();

		$this->assertTrue($callbackWasCalled);
    }
}
