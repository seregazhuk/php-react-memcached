<?php

namespace seregazhuk\React\Memcached\tests\Functional;

use seregazhuk\React\Memcached\Client;
use seregazhuk\React\Memcached\Factory as ClientFactory;

class ClientTest extends WaitTestCase
{
    /**
     * @var Client
     */
    protected $client;


    protected function setUp()
    {
        parent::setUp();
        $this->client = ClientFactory::createClient($this->loop);
    }

    /** @test */
    public function it_stores_and_retrieves_values()
    {
        $setPromise = $this->client->set('key', [12345]);
        $this->waitForPromiseResolves($setPromise);

        $this->assertPromiseResolvesWith([12345], $this->client->get('key'));
    }

    /** @test */
    public function it_flashes_database()
    {
        $this->waitForPromiseResolves($this->client->set('key', 12345));
        $this->waitForPromiseResolves($this->client->flushAll());

        $getPromise = $this->client->get('key');
        $this->waitForPromiseRejects($getPromise);
    }

    /** @test */
    public function it_increments_value()
    {
        $this->waitForPromiseResolves($this->client->set('key', 11));
        $this->waitForPromiseResolves($this->client->incr('key', 1));

        $this->assertPromiseResolvesWith(12, $this->client->get('key'));
    }

    /** @test */
    public function it_decrements_value()
    {
        $this->waitForPromiseResolves($this->client->set('key', 10));
        $this->waitForPromiseResolves($this->client->decr('key', 1));

        $this->assertPromiseResolvesWith(9, $this->client->get('key'));
    }

    /** @test */
    public function it_stores_value_with_expiration()
    {
        $setPromise = $this->client->set('key', [12345], 0 , 1);
        $this->waitForPromiseResolves($setPromise);

        sleep(2);

        $getPromise = $this->client->get('key');
        $this->waitForPromiseRejects($getPromise);
    }

    /** @test */
    public function it_deletes_key()
    {
        $setPromise = $this->client->set('key', [12345], 0 , 1);
        $this->waitForPromiseResolves($setPromise);

        $this->waitForPromiseResolves($this->client->delete('key'));

        $getPromise = $this->client->get('key');
        $this->waitForPromiseRejects($getPromise);
    }

    /** @test */
    public function it_replaces_value()
    {
        $setPromise = $this->client->set('key', [12345], 0 , 1);
        $this->waitForPromiseResolves($setPromise);
        $this->waitForPromiseResolves($this->client->replace('key', 'new value'));

        $getPromise = $this->client->get('key');
        $this->assertPromiseResolvesWith('new value', $getPromise);
    }

    /** @test */
    public function it_touches_key()
    {
        $setPromise = $this->client->set('key', [12345], 0 , 1);
        $this->waitForPromiseResolves($setPromise);
        $this->waitForPromiseResolves($this->client->touch('key', 10));

        $getPromise = $this->client->get('key');
        $this->assertPromiseResolvesWith([12345], $getPromise);
    }

    /** @test */
    public function it_retrieves_server_stats()
    {
        $stats = $this->waitForPromiseResolves($this->client->stats());
        $this->assertInternalType('array', $stats);
        $this->arrayHasKey('pid', $stats);
    }
}
