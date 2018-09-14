<?php

namespace seregazhuk\React\Memcached\Protocol\Response;

use seregazhuk\React\Memcached\Protocol\Parser;

class VersionResponse extends Response
{
    /**
     * {@inheritdoc}
     */
    public function parse()
    {
        return trim(str_replace(Parser::RESPONSE_VERSION, '', $this->data));
    }
}
