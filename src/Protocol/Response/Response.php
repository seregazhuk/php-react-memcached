<?php

namespace seregazhuk\React\Memcached\Protocol\Response;

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
     * @return string|bool|null
     */
    abstract public function parse();
}
