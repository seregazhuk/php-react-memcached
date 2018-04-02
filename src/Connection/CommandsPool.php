<?php

namespace seregazhuk\React\Memcached\Connection;

class CommandsPool
{
    /**
     * @var string[]
     */
    private $commands = [];

    /**
     * @param string $command
     */
    public function add($command)
    {
        $this->commands[] = $command;
    }

    public function clear()
    {
        $this->commands = [];
    }

    /**
     * @return string
     */
    public function shift()
    {
        return array_shift($this->commands);
    }
}
