<?php

namespace seregazhuk\React\Memcached\Connection;

class QueriesPool
{
    /**
     * @var string[]
     */
    protected $queries = [];

    /**
     * @param string $query
     */
    public function add($query)
    {
        $this->queries[] = $query;
    }

    public function clear()
    {
        $this->queries = [];
    }

    /**
     * @return string
     */
    public function shift()
    {
        return array_shift($this->queries);
    }
}
