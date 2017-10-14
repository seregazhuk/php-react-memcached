<?php

namespace seregazhuk\React\Memcached\Protocol\Request;

use seregazhuk\React\Memcached\Protocol\Parser;

class StorageRequest extends Request
{
    /**
     * @param string $command
     * @param string $key
     * @param mixed $value
     * @param int $flags
     * @param int $expiration
     */
    public function __construct($command, $key, $value, $flags = 0, $expiration = 0)
    {
        // Serialize non-numeric values. Numeric values should stay as they are
        // because they could be incremented/decremented.
        $value = is_numeric($value) ? $value : serialize($value);
        
        $command = implode(' ', [$command, $key, $flags, $expiration, strlen($value)]);

        $this->command = $command . Parser::COMMAND_SEPARATOR . $value . Parser::COMMAND_SEPARATOR;
    }
}
