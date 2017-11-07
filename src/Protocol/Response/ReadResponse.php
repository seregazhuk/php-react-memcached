<?php

namespace seregazhuk\React\Memcached\Protocol\Response;

use seregazhuk\React\Memcached\Protocol\Parser;

class ReadResponse extends Response
{
    /**
     * {@inheritdoc}
     */
    public function parse()
    {
        $regExp = '/VALUE \w+ \d+ \d+' . Parser::COMMAND_SEPARATOR . '(.*)' . Parser::COMMAND_SEPARATOR . 'END/';
        preg_match($regExp, $this->data, $match);

        $value = isset($match[1]) ? trim($match[1]) : null;

        if(null === $value) {
            $this->fail();
        }

        // Unserialize non-numeric values
        return is_numeric($value) ? $value : unserialize($value);
    }
}
