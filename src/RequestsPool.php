<?php

namespace seregazhuk\React\Memcached;

use seregazhuk\React\Memcached\Exception\Exception;

class RequestsPool
{
    /**
     * @var Request[]
     */
    protected $requests = [];

    /**
     * @param Request $request
     */
    public function add(Request $request)
    {
        $this->requests[] = $request;
    }

    /**
     * @return Request
     */
    public function shift()
    {
        return array_shift($this->requests);
    }

    /**
     * @param Exception $exception
     */
    public function rejectAllWith(Exception $exception)
    {
        while (!$this->isEmpty()) {
            $request = $this->shift();
            $request->reject($exception);
        }
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->requests);
    }
}
