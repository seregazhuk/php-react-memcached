<?php

namespace seregazhuk\React\Memcached\Protocol\Response;

use seregazhuk\React\Memcached\Protocol\Parser;

class VerbosityResponse extends Response
{
    /**
     * {@inheritdoc}
     */
    public function parse()
    {
        return trim($this->data) === Parser::RESPONSE_OK;
    }
}
