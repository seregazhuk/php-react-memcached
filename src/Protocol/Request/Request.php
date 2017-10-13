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
    public function getCommand() {
        return $this->command;
    }
}
