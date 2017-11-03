<?php

namespace seregazhuk\React\Memcached\tests\Functional;

use Clue\React\Block;
use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory as LoopFactory;
use React\EventLoop\LoopInterface;
use seregazhuk\React\Memcached\Client;
use seregazhuk\React\Memcached\Factory as ClientFactory;

class ClientTest extends TestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var LoopInterface
     */
    protected $loop;

    protected function setUp()
    {
        $this->loop = LoopFactory::create();
        $this->client = ClientFactory::createClient($this->loop);
    }

    /** @test */
    public function it_stores_and_retrieves_values()
    {
        $setPromise = $this->client->set('key', 12345);
        Block\await($setPromise, $this->loop);

        $getPromise = $this->client->get('key');
        $this->assertEquals(12345, Block\await($getPromise, $this->loop));
    }
}
