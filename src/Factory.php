<?php

namespace seregazhuk\React\Memcached;

use React\EventLoop\LoopInterface;
use React\Socket\Connector;
use seregazhuk\React\Memcached\Protocol\Parser;
use seregazhuk\React\Memcached\Protocol\Response\Factory as ResponseFactory;
use seregazhuk\React\Memcached\Protocol\Request\Factory as RequestFactory;

class Factory
{
    /**
     * Creates a memcached client
     * @param LoopInterface $loop
     * @return Client
     */
    public static function createClient(LoopInterface $loop)
    {
        return new Client(new Connector($loop), self::createProtocolParser());
    }

    /**
     * @return Parser
     */
    protected static function createProtocolParser()
    {
        return new Parser(new RequestFactory(), new ResponseFactory());
    }
}
