<?php

namespace seregazhuk\React\Memcached\Protocol\Response;

use seregazhuk\React\Memcached\Exception\FailedCommandException;
use seregazhuk\React\Memcached\Protocol\Parser;

class DeleteResponse extends Response
{
    /**
     * @return bool
     * @throws FailedCommandException
     */
    public function parse()
    {
        if (trim($this->data) !== Parser::RESPONSE_DELETED) {
            $this->fail();
        }

        return true;
    }
}
