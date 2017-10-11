<?php

namespace seregazhuk\React\Memcached;

use LogicException;
use React\Promise\Deferred;
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
     * @var Deferred[]
     */
    private $requests = [];

    /**
     * @param DuplexStreamInterface $stream
     * @param ProtocolParser $parser
     */
    public function __construct(DuplexStreamInterface $stream, ProtocolParser $parser)
    {
        $stream->on('data', function ($chunk) use ($parser) {
            $parsed = $parser->parseResponse($chunk);
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
        $request = new Deferred();

        $query = $this->parser->makeRequest($name, $args);
        $this->stream->write($query);
        $this->requests[] = $request;

        return $request->promise();
    }

    /**
     * @param array $data
     */
    protected function handleData(array $data)
    {
        if (!$this->requests) {
            throw new LogicException('Unexpected reply received, no matching request found');
        }

        foreach ($data as $response) {
            /* @var $request Deferred */
            $request = array_shift($this->requests);
            $request->resolve($response);
        }
    }
}
