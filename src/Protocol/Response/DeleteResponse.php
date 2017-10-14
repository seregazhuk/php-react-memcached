<?php

namespace seregazhuk\React\Memcached\Protocol\Response;

use seregazhuk\React\Memcached\Protocol\Parser;

class DeleteResponse extends Response
{
    /**
     * @return bool
     */
    public function parse()
    {
        return trim($this->data) === Parser::RESPONSE_DELETED;
    }
}
