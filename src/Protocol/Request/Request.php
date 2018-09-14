<?php

namespace seregazhuk\React\Memcached\Protocol\Request;

abstract class Request
{
    /**
     * @var string
     */
    protected $command;

    public function command(): string
    {
        return $this->command;
    }
}
