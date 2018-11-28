<?php

namespace seregazhuk\React\Memcached;

use Evenement\EventEmitter;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use seregazhuk\React\Memcached\Connection\Connection;
use seregazhuk\React\Memcached\Exception\ConnectionClosedException;
use seregazhuk\React\Memcached\Exception\Exception;
use seregazhuk\React\Memcached\Exception\WrongCommandException;
use seregazhuk\React\Memcached\Protocol\Parser;
use seregazhuk\React\Memcached\Request\Request;
use seregazhuk\React\Memcached\Request\RequestsPool;

/**
 * @method PromiseInterface set(string $key, mixed $value, int $flag = 0, int $exp = 0)
 * @method PromiseInterface version()
 * @method PromiseInterface verbosity(int $level)
 * @method PromiseInterface flushAll()
 * @method PromiseInterface get($key)
 * @method PromiseInterface delete($key)
 * @method PromiseInterface replace($key, $value)
 * @method PromiseInterface incr($key, $value)
 * @method PromiseInterface decr($key, $value)
 * @method PromiseInterface stats()
 * @method PromiseInterface touch($key, $exp)
 * @method PromiseInterface add($key, $value)
 */
class Client extends EventEmitter
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var RequestsPool
     */
    private $pool;

    /**
     * Indicates that the connection is closed.
     *
     * @var bool
     */
    private $isClosed = false;

    /**
     * Indicates that we don't accept new requests but we are still waiting for
     * pending requests to be resolved.
     *
     * @var bool
     */
    private $isEnding = false;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     * @param Parser $parser
     */
    public function __construct(Connection $connection, Parser $parser)
    {
        $this->parser = $parser;
        $this->connection = $connection;
        $this->pool = new RequestsPool();

        $this->setConnectionHandlers();
    }

    protected function setConnectionHandlers(): void
    {
        $this->connection->on('data', function ($chunk) {
            $parsed = $this->parser->parseRawResponse($chunk);
            $this->resolveRequests($parsed);
        });

        $this->connection->on('failed', function () {
            $this->pool->rejectAll(new ConnectionClosedException());
        });

        $this->connection->on('close', function () {
            if (!$this->isEnding) {
                $this->emit('error', [new ConnectionClosedException()]);
            }
        });
    }

    /**
     * @param string $name
     * @param array $args
     * @return Promise|PromiseInterface
     */
    public function __call(string $name, array $args)
    {
        $request = new Request($name);

        if ($this->isEnding) {
            $request->reject(new ConnectionClosedException());
        } else {
            try {
                $command = $this->parser->makeCommand($name, $args);
                $this->connection->write($command);
                $this->pool->add($request);
            } catch (WrongCommandException $e) {
                $request->reject($e);
            }
        }

        return $request->promise();
    }

    /**
     * @param string[] $responses
     * @throws Exception
     */
    public function resolveRequests(array $responses): void
    {
        if ($this->pool->isEmpty()) {
            throw new Exception('Received unexpected response, no matching request found');
        }

        foreach ($responses as $response) {
            $request = $this->pool->shift();

            try {
                $parsedResponse = $this->parser->parseResponse($request->command(), $response);
                $request->resolve($parsedResponse);
            } catch (WrongCommandException $exception) {
                $request->reject($exception);
            }
        }

        if ($this->isEnding && $this->pool->isEmpty()) {
            $this->close();
        }
    }

    /**
     * Forces closing the connection and rejects all pending requests
     */
    public function close(): void
    {
        if ($this->isClosed) {
            return;
        }

        $this->isEnding = true;
        $this->isClosed = true;

        $this->connection->close();
        $this->emit('close');

        $this->pool->rejectAll(new ConnectionClosedException());
    }
}
