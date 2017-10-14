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

        $value = isset($match[1]) ? $match[1] : null;

        return $value ? unserialize($value) : null;
    }
}
