<?php

namespace seregazhuk\React\Memcached;

use LogicException;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use React\Stream\DuplexStreamInterface;
use seregazhuk\React\Memcached\Protocol\Parser;

class Client
{
    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var DuplexStreamInterface
     */
    private $stream;

    /**
     * @var Request[]
     */
    private $requests = [];

    /**
     * @param DuplexStreamInterface $stream
     * @param Parser $parser
     */
    public function __construct(DuplexStreamInterface $stream, Parser $parser)
    {
        $this->stream = $stream;
        $this->parser = $parser;

        $stream->on('data', function ($chunk) {
            $parsed = $this->parser->parseRawResponse($chunk);
            $this->resolveRequests($parsed);
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

        $query = $this->parser->makeRequest($name, $args);
        $this->stream->write($query);
        $this->requests[] = $request;

        return $request->getPromise();
    }

    /**
     * @param array $responses
     */
    protected function resolveRequests(array $responses)
    {
        if (empty($this->requests)) {
            throw new LogicException('Received unexpected response, no matching request found');
        }

        foreach ($responses as $response) {
            /* @var $request Request */
            $request = array_shift($this->requests);

            $parsedResponse = $this->parser->parseResponse($request->getCommand(), $response);
            $request->resolve($parsedResponse);
        }
    }
}
