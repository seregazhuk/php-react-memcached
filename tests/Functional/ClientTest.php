<?php

namespace seregazhuk\React\Memcached\tests\Functional;

use seregazhuk\React\Memcached\Client;
use seregazhuk\React\Memcached\Factory as ClientFactory;
use seregazhuk\React\PromiseTesting\TestCase;

class ClientTest extends TestCase
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
        $this->client->set('key', [12345]);
        $this->assertPromiseResolvesWith($this->client->get('key'), [12345]);
    }

    /** @test */
    public function it_flashes_database()
    {
        $this->waitForPromiseToResolve($this->client->set('key', 12345));
        $this->waitForPromiseToResolve($this->client->flushAll());

        $this->assertPromiseRejects($this->client->get('key'));
    }

    /** @test */
    public function it_increments_value()
    {
        $this->waitForPromiseToResolve($this->client->set('key', 11));
        $this->waitForPromiseToResolve($this->client->incr('key', 1));

        $this->assertPromiseResolvesWith($this->client->get('key'), 12);
    }

    /** @test */
    public function it_decrements_value()
    {
        $this->waitForPromiseToResolve($this->client->set('key', 10));
        $this->waitForPromiseToResolve($this->client->decr('key', 1));

        $this->assertPromiseResolvesWith($this->client->get('key'), 9);
    }

    /** @test */
    public function it_stores_value_with_expiration()
    {
        $this->waitForPromise($this->client->set('key', [12345], 0 , 1));

        sleep(2);

        $this->assertPromiseRejects($this->client->get('key'));
    }

    /** @test */
    public function it_deletes_key()
    {
        $this->waitForPromiseToResolve($this->client->set('key', [12345], 0 , 1));
        $this->waitForPromiseToResolve($this->client->delete('key'));

        $this->assertPromiseRejects($this->client->get('key'));
    }

    /** @test */
    public function it_replaces_value()
    {
        $this->waitForPromiseToResolve($this->client->set('key', [12345], 0 , 1));
        $this->waitForPromiseToResolve($this->client->replace('key', 'new value'));

        $this->assertPromiseResolvesWith($this->client->get('key'), 'new value');
    }

    /** @test */
    public function it_touches_key()
    {
        $this->waitForPromiseToResolve($this->client->set('key', [12345], 0 , 1));
        $this->waitForPromiseToResolve($this->client->touch('key', 10));

        $getPromise = $this->client->get('key');
        $this->assertPromiseResolvesWith($getPromise, [12345]);
    }

    /** @test */
    public function it_retrieves_server_stats()
    {
        $stats = $this->waitForPromise($this->client->stats());
        $this->assertInternalType('array', $stats);
        $this->arrayHasKey('pid', $stats);
    }
}
