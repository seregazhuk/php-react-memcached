<?php

namespace seregazhuk\React\Memcached\Protocol\Response;

use seregazhuk\React\Memcached\Protocol\Parser;

class WriteResponse extends Response
{
    /**
     * {@inheritdoc}
     */
    public function parse()
    {
        return $this->data === Parser::RESPONSE_STORED;
    }
}
