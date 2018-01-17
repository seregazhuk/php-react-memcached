<?php

namespace seregazhuk\React\Memcached\Protocol\Response;

use seregazhuk\React\Memcached\Protocol\Parser;

class StatsResponse extends Response
{
    /**
     * {@inheritdoc}
     */
    public function parse()
    {
        $lines = explode(Parser::COMMAND_SEPARATOR, $this->data);
        $stats = [];

        foreach ($lines as $line) {
            preg_match('/STAT (\w+) (\w+)/', $line, $matches);
            if (empty($matches)) {
                break;
            }

            $stats[$matches[1]] = $matches[2];
        }

        return $stats;
    }
}
