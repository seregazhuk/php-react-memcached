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
     * @var DuplexStreamInterface
     */
    protected $stream;

    /**
     * @var string
     */
    protected $address;

    /**
     * @var ConnectorInterface
     */
    protected $connector;

    /**
     * @var bool
     */
    protected $isConnecting = false;

    /**
     * @var QueriesPool
     */
    protected $queriesPool;

    /**
     * @param string $address
     * @param ConnectorInterface $connector
     */
    public function __construct($address, ConnectorInterface $connector)
    {
        $this->address = $address;
        $this->connector = $connector;
        $this->queriesPool = new QueriesPool();
    }

    /**
     * @return PromiseInterface
     */
    public function connect()
    {
        $this->isConnecting = true;

        return $this->connector
            ->connect($this->address)
            ->then(
                [$this, 'onConnected'],
                [$this, 'onFailed']
            );
    }

    /**
     * @param ConnectionInterface $stream
     */
    public function onConnected(ConnectionInterface $stream)
    {
        $this->stream = $stream;
        $this->isConnecting = false;

        $stream->on('data', function ($chunk) {
            $this->emit('data', [$chunk]);
        });

        $stream->on('close', [$this, 'close']);

        while ($query = $this->queriesPool->shift()) {
            $this->stream->write($query);
        }
    }

    public function onFailed()
    {
        $this->cancelConnecting();
        $this->emit('failed');
    }

    public function close()
    {
        if ($this->stream) {
            $this->stream->close();
        }

        $this->cancelConnecting();
        $this->emit('close');
    }

    /**
     * @param string $query
     */
    public function write($query)
    {
        if ($this->stream && $this->stream->isWritable()) {
            $this->stream->write($query);
            return;
        }

        $this->queriesPool->add($query);
        if (!$this->isConnecting) {
            $this->connect();
        }
    }

    private function cancelConnecting()
    {
        $this->isConnecting = false;
        $this->queriesPool->clear();
    }
}
