<?php

namespace seregazhuk\React\Memcached\Protocol\Response;

use seregazhuk\React\Memcached\Protocol\Parser;

class Factory
{
    /**
     * @param string $command
     * @param string $data
     * @return Response
     */
    public function makeByCommand($command, $data)
    {
        if(in_array($command, Parser::RETRIEVAL_COMMANDS)) {
            return new ReadResponse($data);
        }

        if(in_array($command, Parser::STORAGE_COMMANDS)) {
            return new WriteResponse($data);
        }

        if($command == Parser::COMMAND_VERSION)  {
            return new VersionResponse($data);
        }
    }
}
