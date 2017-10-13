<?php

namespace seregazhuk\React\Memcached\Protocol\Request;

use seregazhuk\React\Memcached\Protocol\Parser;

class RetrievalRequest extends Request
{
    /**
     * @param string $command
     * @param array $args
     */
    public function __construct($command, $args)
    {
        $this->command = $command . ' ' . implode(' ' , $args) . Parser::COMMAND_SEPARATOR;
    }
}
