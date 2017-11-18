<?php

namespace seregazhuk\React\Memcached\Protocol\Request;

use seregazhuk\React\Memcached\Protocol\Parser;

class SimpleRequest extends Request
{
    /**
     * @param string $command
     * @param array $args
     */
    public function __construct($command, $args)
    {
        $command = $this->camelCaseToUnderScore($command);
        $args = empty($args) ? '' : ' ' . implode(' ' , $args);
        $this->command = $command . $args . Parser::COMMAND_SEPARATOR;
    }

    /**
     * @param $string
     * @return string
     */
    private function camelCaseToUnderScore($string)
    {
        return strtolower(ltrim(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $string), '_'));
    }
}
