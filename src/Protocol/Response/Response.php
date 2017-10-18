<?php

namespace seregazhuk\React\Memcached\Protocol\Response;

use seregazhuk\React\Memcached\Exception\FailedCommandException;

abstract class Response
{
    /**
     * @var string
     */
    protected $data;

    /**
     * @param string $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     * @throws FailedCommandException
     */
    abstract public function parse();

    /**
     * @throws FailedCommandException
     */
    protected function fail()
    {
        throw new FailedCommandException($this->data);
    }
}
