<?php

namespace seregazhuk\React\Memcached\tests;

use Mockery;
use Mockery\MockInterface;
use React\Stream\DuplexStreamInterface;
use seregazhuk\React\Memcached\Client;
use seregazhuk\React\Memcached\Connection;
use seregazhuk\React\Memcached\Exception\ConnectionClosedException;
use seregazhuk\React\Memcached\Exception\Exception;
use seregazhuk\React\Memcached\Exception\WrongCommandException;
use seregazhuk\React\Memcached\Protocol\Parser;
use seregazhuk\React\Memcached\Protocol\Request\Factory as RequestFactory;
use seregazhuk\React\Memcached\Protocol\Response\Factory as ResponseFactory;

class ClientTest extends PromiseTestCase
{
    /**
     * @var Parser|MockInterface
     */
    protected $parser;

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
        $this->parser = Mockery::mock(Parser::class, [new RequestFactory(), new ResponseFactory()])->makePartial();
        $this->connection = Mockery::mock(Connection::class)->shouldReceive('on')->getMock();
        $this->client = new Client($this->connection, $this->parser);

        parent::setUp();
    }

    /** @test */
    public function it_sends_data_to_the_connection()
    {
        $this->parser->shouldReceive('makeRequest')->andReturn("version\n\r");
        $this->connection->shouldReceive('write')->with("version\n\r")->once();
        $this->client->version();
    }

    /** @test */
    public function it_rejects_a_promise_when_unsupported_command_is_called()
    {
        $promise = $this->client->not_valid();
        $this->expectPromiseRejectsWith($promise, WrongCommandException::class);
    }

    /** @test */
    public function it_resolves_a_promise_with_data_from_response()
    {
        $this->parser->shouldReceive('makeRequest')->once();
        $this->connection->shouldReceive('write')->once();
        $promise = $this->client->version();

        $this->client->resolveRequests(['12345']);

        $this->expectPromiseResolvesWith($promise, '12345');
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
        $this->parser->shouldReceive('makeRequest')->once();
        $this->connection->shouldReceive('write')->once();
        $this->connection->shouldReceive('close')->once();
        $promise = $this->client->version();

        $this->client->close();

        $this->expectPromiseRejectsWith($promise, ConnectionClosedException::class);
    }

    /** @test */
    public function it_rejects_all_new_requests_when_closed()
    {
        $this->parser->shouldNotReceive('makeRequest');
        $this->connection->shouldReceive('close')->once();

        $this->client->close();
        $promise = $this->client->version();
        $this->expectPromiseRejectsWith($promise, ConnectionClosedException::class);
    }

    /** @test */
    public function it_rejects_all_new_requests_when_ending()
    {
        $this->parser->shouldNotReceive('makeRequest');
        $this->connection->shouldReceive('close')->once();

        $this->client->end();
        $promise = $this->client->version();
        $this->expectPromiseRejectsWith($promise, ConnectionClosedException::class);
    }

	/** @test */
	public function it_emits_close_event_when_closing()
	{
		$this->connection->shouldReceive('close')->once();

		$this->client->on('close', $this->expectCallableOnce());
		$this->client->end();
    }
}
