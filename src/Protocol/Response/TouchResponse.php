<?php

namespace seregazhuk\React\Memcached\Protocol\Response;

use seregazhuk\React\Memcached\Exception\FailedCommandException;
use seregazhuk\React\Memcached\Protocol\Parser;

class TouchResponse extends Response
{
    /**
     * {@inheritdoc}
     */
    public function parse()
    {
        if (trim($this->data) !== Parser::RESPONSE_TOUCHED) {
            $this->fail();
        }

        return true;
    }
}
