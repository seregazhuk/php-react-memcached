<?php

namespace seregazhuk\React\Memcached\Protocol\Response;

class TouchResponse extends Response
{
    /**
     * @return mixed
     */
    public function parse()
    {
        return trim($this->data) === 'TOUCHED';
    }
}
