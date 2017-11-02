<?php

namespace seregazhuk\React\Memcached;

use Evenement\EventEmitter;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ConnectorInterface;
use React\Stream\DuplexStreamInterface;
use seregazhuk\React\Memcached\Exception\ConnectionClosedException;
use seregazhuk\React\Memcached\Exception\ConnectionFailedException;
use seregazhuk\React\Memcached\Exception\Exception;
use seregazhuk\React\Memcached\Exception\FailedCommandException;
use seregazhuk\React\Memcached\Exception\WrongCommandException;
use seregazhuk\React\Memcached\Protocol\Parser;

/**
 * @method PromiseInterface set(string $key, mixed $value)
 * @method PromiseInterface version()
 * @method PromiseInterface verbosity(int $level)
 * @method PromiseInterface flushAll()
 * @method PromiseInterface get($key)
 * @method PromiseInterface delete($key)
 * @method PromiseInterface replace($key, $value)
 * @method PromiseInterface incr($key, $value)
 * @method PromiseInterface decr($key, $value)
 * @method PromiseInterface stats()
 * @method PromiseInterface touch($key)
 * @method PromiseInterface add($key, $value)
 */
class Client extends EventEmitter
{
    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var DuplexStreamInterface
     */
    protected $connector;

    /**
     * @var DuplexStreamInterface
     */
    protected $stream;

    /**
     * @var Request[]
     */
    protected $requests = [];

    /**
     * Indicates that the connection is closed.
     *
     * @var bool
     */
    protected $isClosed = false;

    /**
     * Indicates that we don't accept new requests but we are still waiting for
     * pending requests to be resolved.
     *
     * @var bool
     */
    protected $isEnding = false;

    /**
     * @var string[]
     */
    protected $queries = [];

    /**
     * @var bool
     */
    protected $isConnecting = false;

    /**
     * @var string
     */
    protected $address;

    /**
     * @param string $address
     * @param ConnectorInterface $connector
     * @param Parser $parser
     */
    public function __construct($address, ConnectorInterface $connector, Parser $parser)
    {
        $this->connector = $connector;
        $this->parser = $parser;
        $this->address = $address;
    }

    /**
     * @param string $name
     * @param array $args
     * @return Promise|PromiseInterface
     */
    public function __call($name, $args)
    {
        $request = new Request($name);

        if($this->isEnding) {
            $request->reject(new ConnectionClosedException());
        } else {
            try {
                $query = $this->parser->makeRequest($name, $args);
                $this->write($query);
                $this->requests[] = $request;
            } catch (WrongCommandException $e) {
                $request->reject($e);
            }
        }

        return $request->getPromise();
    }

    /**
     * @param string $query
     */
    protected function write($query)
    {
        if($this->stream) {
            $this->stream->write($query);
            return;
        }

        $this->queries[] = $query;
        if(!$this->isConnecting) {
            $this->connect();
        }
    }

    /**
     * @param array $responses
     * @throws Exception
     */
    public function resolveRequests(array $responses)
    {
        if (empty($this->requests)) {
            throw new Exception('Received unexpected response, no matching request found');
        }

        foreach ($responses as $response) {
            /* @var $request Request */
            $request = array_shift($this->requests);

            try {
                $parsedResponse = $this->parser->parseResponse($request->getCommand(), $response);
                $request->resolve($parsedResponse);
            } catch (FailedCommandException $exception) {
                $request->reject($exception);
            }
        }

        if ($this->isEnding && !$this->requests) {
            $this->close();
        }
    }

    /**
     * Closes the connection when all requests are resolved
     */
    public function end()
    {
        $this->isEnding = true;

        if (!$this->requests) {
            $this->close();
        }
    }

    /**
     * Forces closing the connection and rejects all pending requests
     */
    public function close()
    {
        if ($this->isClosed) {
            return;
        }

        $this->isEnding = true;
        $this->isClosed = true;

        if($this->stream) {
            $this->stream->close();
        }
        $this->emit('close');

        // reject all pending requests
        while($this->requests) {
            $request = array_shift($this->requests);
            /* @var $request Request */
            $request->reject(new ConnectionClosedException());
        }
    }

    /**
     * @param string $address
     * @return PromiseInterface
     */
    public function connect($address = '')
    {
        if(!empty($address)) {
            $this->address = $address;
        }

        $this->isConnecting = true;

        return $this->connector
            ->connect($this->address)
            ->then(
                [$this, 'onConnected'],
                [$this, 'onConnectionFailed']
            );
    }

    /**
     * @param ConnectionInterface $stream
     */
    public function onConnected(ConnectionInterface $stream)
    {
        $this->stream = $stream;
        $this->isConnecting = false;

        // write all pending queries
        while ($this->queries) {
            $query = array_shift($this->queries);
            $this->stream->write($query);
        }

        $stream->on('data', function ($chunk) {
            $parsed = $this->parser->parseRawResponse($chunk);
            $this->resolveRequests($parsed);
        });

        $stream->on('close', function() {
            if(!$this->isEnding) {
                $this->emit('error', [new ConnectionClosedException()]);
                $this->close();
            }
        });
    }

    public function onConnectionFailed()
    {
        $this->isConnecting = false;
        // reject all pending requests
        while($this->requests) {
            $request = array_shift($this->requests);
            /* @var $request Request */
            $request->reject(new ConnectionFailedException());
        }

        $this->queries = [];
    }
}
