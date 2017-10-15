<?php

namespace tests;

use Mockery;
use Mockery\MockInterface;
use React\Stream\DuplexStreamInterface;
use seregazhuk\React\Memcached\Client;
use seregazhuk\React\Memcached\Protocol\Parser;

class StreamingClientTest extends TestCase
{
    /**
     * @var DuplexStreamInterface|MockInterface
     */
    protected $stream;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Parser|MockInterface
     */
    protected $parser;

    protected function setUp()
    {
        $this->stream = Mockery::mock(DuplexStreamInterface::class)->shouldReceive('on')->getMock();
        $this->parser = Mockery::mock(Parser::class);
        $this->client = new Client($this->stream, $this->parser);

        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
        if ($container = Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }

        Mockery::close();
    }

    /** @test */
    public function it_sends_data_to_the_connection()
    {
        $this->parser->shouldReceive('makeRequest')->andReturn("version\n\r");
        $this->stream->shouldReceive('write')->with("version\n\r")->once();
        $this->client->version();
    }
}
