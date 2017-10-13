<?php

namespace seregazhuk\React\Memcached\Protocol\Request;

use seregazhuk\React\Memcached\Protocol\Parser;

class Factory
{
    /**
     * @param string $command
     * @param array $args
     * @return Request
     */
    public function create($command, $args)
    {
        if(in_array($command, Parser::STORAGE_COMMANDS)) {
            return new StorageRequest($command, ...$args);
        }

        return new RetrievalRequest($command, $args);
    }
}
