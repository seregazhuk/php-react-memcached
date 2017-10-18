<?php

namespace seregazhuk\React\Memcached\Protocol\Response;

use seregazhuk\React\Memcached\Protocol\Parser;

class OkResponse extends Response
{
    /**
     * {@inheritdoc}
     */
    public function parse()
    {
        if(trim($this->data) !== Parser::RESPONSE_OK) {
            $this->fail();
        }

        return true;
    }
}
