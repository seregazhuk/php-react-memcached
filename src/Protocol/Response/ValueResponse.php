<?php

namespace seregazhuk\React\Memcached\Protocol\Response;

class ValueResponse extends Response
{
    /**
     * @return string
     */
    public function parse()
    {
        return trim($this->data);
    }
}
