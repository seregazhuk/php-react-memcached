<?php

namespace seregazhuk\React\Memcached\Exception;

use Throwable;

class ConnectionFailedException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct('Connection failed', $code, $previous);
    }

}
