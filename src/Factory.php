<?php

namespace seregazhuk\React\Memcached;

use React\EventLoop\LoopInterface;
use React\Socket\Connector;
use seregazhuk\React\Memcached\Connection\Connection;
use seregazhuk\React\Memcached\Protocol\Parser;

class Factory
{
    /**
     * Creates a memcached client
     * @param LoopInterface $loop
     * @param string $address
     * @return Client
     */
    public static function createClient(LoopInterface $loop, $address = 'localhost:11211')
    {
        $connection = new Connection($address, new Connector($loop));

        return new Client($connection, new Parser());
    }
}
