<?php

namespace seregazhuk\React\Memcached\Connection;

use Evenement\EventEmitter;
use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ConnectorInterface;
use React\Stream\DuplexStreamInterface;

final class Connection extends EventEmitter
{
    /**
     * @var DuplexStreamInterface
     */
    private $stream;

    private $address;

    private $connector;

    private $isConnecting = false;

    private $commandsPool;

    public function __construct(string $address, ConnectorInterface $connector)
    {
        $this->address = $address;
        $this->connector = $connector;
        $this->commandsPool = new CommandsPool();
    }

    public function connect(): PromiseInterface
    {
        $this->isConnecting = true;

        return $this->connector->connect($this->address)->then(
            [$this, 'onConnected'], [$this, 'onFailed']
        );
    }

    public function onConnected(ConnectionInterface $stream): void
    {
        $this->stream = $stream;
        $this->isConnecting = false;

        $stream->on(
            'data', function ($chunk) {
                $this->emit('data', [$chunk]);
            }
        );

        $stream->on('close', [$this, 'close']);

        while ($command = $this->commandsPool->shift()) {
            $this->stream->write($command);
        }
    }

    public function onFailed(): void
    {
        $this->cancelConnecting();
        $this->emit('failed');
    }

    public function close(): void
    {
        if ($this->stream) {
            $this->stream->close();
        }

        $this->cancelConnecting();
        $this->emit('close');
    }

    public function write(string $command): void
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

    private function cancelConnecting(): void
    {
        $this->isConnecting = false;
        $this->commandsPool->clear();
    }
}
