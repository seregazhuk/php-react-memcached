<?php

namespace seregazhuk\React\Memcached;

use LogicException;
use React\Promise\Deferred;
use React\Stream\DuplexStreamInterface;

class Client
{

    protected $parser;
    private   $stream;
    private   $requests = [];

    public function __construct(DuplexStreamInterface $stream, ProtocolParser $parser)
    {
        $stream->on('data', function ($chunk) use ($parser) {
            $parsed = $parser->parseResponse($chunk);
            $this->handleData($parsed);
        });

        $stream->on('close', [$this, 'close']);
        $this->stream = $stream;
        $this->parser = $parser;
    }

    public function __call($name, $args)
    {
        $request = new Deferred();
        $promise = $request->promise();

        $query = $this->parser->makeRequest($name, $args);
        $this->stream->write($query);
        $this->requests[] = $request;

        return $promise;
    }

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
