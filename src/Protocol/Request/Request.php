<?php

namespace seregazhuk\React\Memcached\Protocol\Request;

abstract class Request
{
    /**
     * @var string
     */
    protected $command;

    /**
     * @return string
     */
    public function command()
    {
        return $this->command;
    }
}
