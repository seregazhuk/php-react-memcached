<?php

namespace seregazhuk\React\Memcached\Protocol\Response;

use seregazhuk\React\Memcached\Exception\FailedCommandException;
use seregazhuk\React\Memcached\Protocol\Parser;

class ValueResponse extends Response
{
    /**
     * @return string
     * @throws FailedCommandException
     */
    public function parse()
    {
        if($this->data === Parser::RESPONSE_NOT_FOUND) {
            $this->fail();
        }

        return trim($this->data);
    }
}
