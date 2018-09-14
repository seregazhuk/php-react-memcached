<?php

namespace seregazhuk\React\Memcached\Connection;

final class CommandsPool
{
    /**
     * @var string[]
     */
    private $commands = [];

    public function add(string $command): void
    {
        $this->commands[] = $command;
    }

    public function clear(): void
    {
        $this->commands = [];
    }

    public function shift(): string
    {
        return array_shift($this->commands);
    }
}
