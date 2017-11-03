<?php

namespace seregazhuk\React\Memcached;

use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use React\Socket\Connector;
use React\Socket\ConnectorInterface;
use seregazhuk\React\Memcached\Exception\ConnectionClosedException;
use seregazhuk\React\Memcached\Exception\Exception;
use seregazhuk\React\Memcached\Exception\FailedCommandException;
use seregazhuk\React\Memcached\Exception\WrongCommandException;
use seregazhuk\React\Memcached\Protocol\Parser;
use seregazhuk\React\Memcached\Protocol\Response\Factory as ResponseFactory;
use seregazhuk\React\Memcached\Protocol\Request\Factory as RequestFactory;

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
 * @method PromiseInterface touch($key, $exp)
 * @method PromiseInterface add($key, $value)
 */
class Client extends EventEmitter
{
    /**
     * @var Parser
     */
    protected $parser;

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
     * @var Connection
     */
    protected $connection;

    /**
     * @param LoopInterface $loop
     * @param string $address
     * @param ConnectorInterface|null $connector
     */
    public function __construct(LoopInterface $loop, $address = 'localhost:11211', ConnectorInterface $connector = null)
    {
        if($connector === null) {
            $connector = new Connector($loop);
        }

        $this->parser = $this->createProtocolParser();
        $this->connection = new Connection($address, $connector);

        $this->setConnectionHandlers();
    }

    /**
     * @return Parser
     */
    protected function createProtocolParser()
    {
        return new Parser(new RequestFactory(), new ResponseFactory());
    }

    protected function setConnectionHandlers()
    {
        $this->connection->on('data', function ($chunk) {
            $parsed = $this->parser->parseRawResponse($chunk);
            $this->resolveRequests($parsed);
        });

        $this->connection->on('failed', function() {
            $this->rejectPendingRequestsWith(new ConnectionClosedException());
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
    public function __call($name, $args)
    {
        $request = new Request($name);

        if($this->isEnding) {
            $request->reject(new ConnectionClosedException());
        } else {
            try {
                $query = $this->parser->makeRequest($name, $args);
                $this->connection->write($query);
                $this->requests[] = $request;
            } catch (WrongCommandException $e) {
                $request->reject($e);
            }
        }

        return $request->getPromise();
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

        $this->connection->close();
        $this->emit('close');

        $this->rejectPendingRequestsWith(new ConnectionClosedException());
    }

    /**
     * @param Exception $exception
     */
    protected function rejectPendingRequestsWith(Exception $exception)
    {
        while($this->requests) {
            $request = array_shift($this->requests);
            /* @var $request Request */
            $request->reject($exception);
        }
    }
}
