<?php

namespace seregazhuk\React\Memcached;

use Evenement\EventEmitter;
use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ConnectorInterface;
use React\Stream\DuplexStreamInterface;

class Connection extends EventEmitter
{
    /**
     * @var bool
     */
    protected $isConnecting;

    /**
     * @var DuplexStreamInterface
     */
    protected $stream;

    /**
     * @var string[]
     */
    protected $queries = [];

    /**
     * @var string
     */
    protected $address;

    /**
     * @var ConnectorInterface
     */
    protected $connector;

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
        if (!empty($address)) {
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

    public function close()
    {
        if($this->stream) {
            $this->stream->close();
        }
    }

    /**
     * @param ConnectionInterface $stream
     */
    public function onConnected(ConnectionInterface $stream)
    {
        $this->stream = $stream;
        $this->isConnecting = false;
        $this->emit('connected');

        $stream->on('data', function ($chunk) {
            $this->emit('data', [$chunk]);
        });

        $stream->on('close', function () {
            $this->emit('close');
        });

        while($this->queries) {
            $this->stream->write(array_shift($this->queries));
        }
    }

    public function onConnectionFailed()
    {
        $this->isConnecting = false;
        $this->emit('failed');
        $this->queries = [];
    }

    /**
     * @param string $query
     */
    public function write($query)
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
}
