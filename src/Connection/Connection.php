<?php

namespace seregazhuk\React\Memcached\Connection;

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
     * @var CommandsPool
     */
    protected $commandsPool;

    /**
     * @param string $address
     * @param ConnectorInterface $connector
     */
    public function __construct($address, ConnectorInterface $connector)
    {
        $this->address = $address;
        $this->connector = $connector;
        $this->commandsPool = new CommandsPool();
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

        while ($command = $this->commandsPool->shift()) {
            $this->stream->write($command);
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
     * @param string $command
     */
    public function write($command)
    {
        if ($this->stream && $this->stream->isWritable()) {
            $this->stream->write($command);
            return;
        }

        $this->commandsPool->add($command);
        if (!$this->isConnecting) {
            $this->connect();
        }
    }

    private function cancelConnecting()
    {
        $this->isConnecting = false;
        $this->commandsPool->clear();
    }
}
