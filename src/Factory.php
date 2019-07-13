<?php

namespace seregazhuk\React\Memcached;

use React\EventLoop\LoopInterface;
use React\Socket\Connector;
use seregazhuk\React\Memcached\Connection\Connection;
use seregazhuk\React\Memcached\Protocol\Parser;

class Factory
{
    public static function createClient(LoopInterface $loop, string $address = 'localhost:11211'): Client
    {
        $connection = new Connection($address, new Connector($loop));

        return new Client($connection, new Parser());
    }
}
