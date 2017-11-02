<?php

namespace seregazhuk\React\Memcached;

use Evenement\EventEmitter;
use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ConnectorInterface;

class Connection extends EventEmitter
{
    protected $isConnecting;
    protected $stream;
    private   $address;
    /**
     * @var ConnectorInterface
     */
    private $connector;

    /**
     * @param $address
     * @param ConnectorInterface $connector
     */
    public function __construct($address, ConnectorInterface $connector)
    {
        $this->address = $address;
        $this->connector = $connector;
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
            $this->emit([$chunk]);
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
