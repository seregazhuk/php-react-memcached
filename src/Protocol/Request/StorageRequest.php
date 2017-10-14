<?php

namespace seregazhuk\React\Memcached\Protocol\Request;

use seregazhuk\React\Memcached\Protocol\Parser;

class StorageRequest extends Request
{
    /**
     * @param string $command
     * @param string $key
     * @param string $value
     * @param int $flags
     * @param int $expiration
     */
    public function __construct($command, $key, $value, $flags = 0, $expiration = 0)
    {
        $value = serialize($value);
        $command = implode(' ', [$command, $key, $flags, $expiration, strlen($value)]);

        $this->command = $command . Parser::COMMAND_SEPARATOR . $value . Parser::COMMAND_SEPARATOR;
    }
}
