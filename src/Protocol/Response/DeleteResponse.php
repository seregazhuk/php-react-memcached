<?php

namespace seregazhuk\React\Memcached\Protocol\Response;

use seregazhuk\React\Memcached\Protocol\Parser;

class DeleteResponse extends Response
{
    /**
     * {@inheritdoc}
     */
    public function parse(): bool
    {
        if (trim($this->data) !== Parser::RESPONSE_DELETED) {
            $this->fail();
        }

        return true;
    }
}
