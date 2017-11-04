<?php

namespace seregazhuk\React\Memcached;

use Evenement\EventEmitter;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use seregazhuk\React\Memcached\Exception\ConnectionClosedException;
use seregazhuk\React\Memcached\Exception\Exception;
use seregazhuk\React\Memcached\Exception\FailedCommandException;
use seregazhuk\React\Memcached\Exception\WrongCommandException;
use seregazhuk\React\Memcached\Protocol\Parser;

/**
 * @method PromiseInterface set(string $key, mixed $value, int $flag, int $exp)
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
     * @param Connection $connection
     * @param Parser $parser
     */
    public function __construct(Connection $connection, Parser $parser)
    {
        $this->parser = $parser;
        $this->connection = $connection;

        $this->setConnectionHandlers();
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
