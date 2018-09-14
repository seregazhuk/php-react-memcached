<?php

namespace seregazhuk\React\Memcached\Protocol\Request;

use seregazhuk\React\Memcached\Protocol\Parser;

final class SimpleRequest extends Request
{
    public function __construct(string $command, array $args)
    {
        $command = $this->camelCaseToUnderScore($command);
        $parsedArgs = empty($args) ? '' : ' ' . implode(' ', $args);
        $this->command = $command . $parsedArgs . Parser::COMMAND_SEPARATOR;
    }

    private function camelCaseToUnderScore(string $string): string
    {
        return strtolower(ltrim(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $string), '_'));
    }
}
