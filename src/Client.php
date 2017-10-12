<?php

namespace seregazhuk\React\Memcached;

use LogicException;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use React\Stream\DuplexStreamInterface;

class Client
{
    /**
     * @var ProtocolParser
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
     * @param ProtocolParser $parser
     */
    public function __construct(DuplexStreamInterface $stream, ProtocolParser $parser)
    {
        $stream->on('data', function ($chunk) use ($parser) {
            $parsed = $parser->parseRawResponse($chunk);
            $this->handleData($parsed);
        });

        $this->stream = $stream;
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

        $query = $this->parser->makeRequest($name, $args);
        $this->stream->write($query);
        $this->requests[] = $request;

        return $request->getPromise();
    }

    /**
     * @param array $responses
     */
    protected function handleData(array $responses)
    {
        if (!$this->requests) {
            throw new LogicException('Unexpected reply received, no matching request found');
        }

        foreach ($responses as $response) {
            /* @var $request Request */
            $request = array_shift($this->requests);

            $parsedResponse = $this->parser->parseResponse($request->getCommand(), $response);
            $request->resolve($parsedResponse);
        }
    }
}
