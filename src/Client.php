<?php

namespace seregazhuk\React\Memcached;

use React\Promise\Deferred;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ConnectorInterface;
use React\Stream\DuplexStreamInterface;
use seregazhuk\React\Memcached\Exception\CommandException;
use seregazhuk\React\Memcached\Exception\ConnectionClosedException;
use seregazhuk\React\Memcached\Exception\Exception;
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
class Client
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
     * @var bool
     */
    protected $isClosed = false;

    /**
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
    protected $connecting = false;

    /**
     * @var string
     */
    protected $address;

    /**
     * @param ConnectorInterface $connector
     * @param Parser $parser
     */
    public function __construct(ConnectorInterface $connector, Parser $parser)
    {
        $this->connector = $connector;
        $this->parser = $parser;
    }

    /**
     * @param string $name
     * @param array $args
     * @return Promise|PromiseInterface
     */
    public function __call($name, $args)
    {
        $request = new Request($name);

        if($this->isClosed) {
            $request->reject(new ConnectionClosedException('Connection closed'));
        } else {
            $query = $this->parser->makeRequest($name, $args);
            $this->write($query);
            $this->requests[] = $request;
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
        if(!$this->connecting) {
            $this->connect($this->address);
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
            } catch (CommandException $exception) {
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

        // reject all pending requests
        while($this->requests) {
            $request = array_shift($this->requests);
            /* @var $request Request */
            $request->reject(new ConnectionClosedException('Connection closing'));
        }
    }

    /**
     * @param string $address
     * @return PromiseInterface
     */
    public function connect($address)
    {
        $this->connecting = true;
        $this->address = $address;

        return $this->connector
            ->connect($this->address)
            ->then(
                [$this, 'initConnection'],
                function() {
                    $this->connecting = false;
                }
            );
    }

    /**
     * @param ConnectionInterface $stream
     */
    public function initConnection(ConnectionInterface $stream)
    {
        $this->stream = $stream;
        $this->connecting = false;

        // write all pending queries
        while ($this->queries) {
            $query = array_shift($this->queries);
            $this->stream->write($query);
        }

        // handle responses
        $stream->on('data', function ($chunk) {
            $parsed = $this->parser->parseRawResponse($chunk);
            $this->resolveRequests($parsed);
        });

        // try to reconnect if connection was broken
        $stream->on('close', function () {
            $this->close();
        });
    }
}
