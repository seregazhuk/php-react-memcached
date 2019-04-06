<?php

namespace seregazhuk\React\Memcached\tests\Functional;

use seregazhuk\React\Memcached\Client;
use seregazhuk\React\Memcached\Factory as ClientFactory;
use seregazhuk\React\PromiseTesting\TestCase;

final class ClientTest extends TestCase
{
    /**
     * @var Client
     */
    private $client;


    protected function setUp(): void
    {
        parent::setUp();
        $this->client = ClientFactory::createClient($this->loop);
    }

    /** @test */
    public function it_stores_and_retrieves_values(): void
    {
        $this->client->set('key', [12345]);
        $this->assertPromiseFulfillsWith($this->client->get('key'), [12345]);
    }

    /** @test */
    public function it_stores_and_retrieves_values_with_prefixed_keys(): void
    {
        $this->client->set('prefix:some-key', [12345]);
        $this->assertPromiseFulfillsWith($this->client->get('prefix:some-key'), [12345]);
    }

    /** @test */
    public function it_flashes_database(): void
    {
        $this->waitForPromiseToFulfill($this->client->set('key', 12345));
        $this->waitForPromiseToFulfill($this->client->flushAll());

        $this->assertPromiseRejects($this->client->get('key'));
    }

    /** @test */
    public function it_increments_value(): void
    {
        $this->waitForPromiseToFulfill($this->client->set('key', 11));
        $this->waitForPromiseToFulfill($this->client->incr('key', 1));

        $this->assertPromiseFulfillsWith($this->client->get('key'), 12);
    }

    /** @test */
    public function it_decrements_value(): void
    {
        $this->waitForPromiseToFulfill($this->client->set('key', 10));
        $this->waitForPromiseToFulfill($this->client->decr('key', 1));

        $this->assertPromiseFulfillsWith($this->client->get('key'), 9);
    }

    /** @test */
    public function it_stores_value_with_expiration(): void
    {
        $this->waitForPromise($this->client->set('key', [12345], 0 , 1));

        sleep(2);

        $this->assertPromiseRejects($this->client->get('key'));
    }

    /** @test */
    public function it_deletes_key(): void
    {
        $this->waitForPromiseToFulfill($this->client->set('key', [12345], 0 , 1));
        $this->waitForPromiseToFulfill($this->client->delete('key'));

        $this->assertPromiseRejects($this->client->get('key'));
    }

    /** @test */
    public function it_replaces_value(): void
    {
        $this->waitForPromiseToFulfill($this->client->set('key', [12345], 0 , 1));
        $this->waitForPromiseToFulfill($this->client->replace('key', 'new value'));

        $this->assertPromiseFulfillsWith($this->client->get('key'), 'new value');
    }

    /** @test */
    public function it_touches_key(): void
    {
        $this->waitForPromiseToFulfill($this->client->set('key', [12345], 0 , 1));
        $this->waitForPromiseToFulfill($this->client->touch('key', 10));

        $getPromise = $this->client->get('key');
        $this->assertPromiseFulfillsWith($getPromise, [12345]);
    }

    /** @test */
    public function it_retrieves_server_stats(): void
    {
        $stats = $this->waitForPromise($this->client->stats());
        $this->assertInternalType('array', $stats);
        $this->arrayHasKey('pid', $stats);
    }
}
