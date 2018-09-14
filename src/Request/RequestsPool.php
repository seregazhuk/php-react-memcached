<?php

namespace seregazhuk\React\Memcached\Request;

use seregazhuk\React\Memcached\Exception\Exception;

final class RequestsPool
{
    /**
     * @var Request[]
     */
    private $requests = [];

    public function add(Request $request): void
    {
        $this->requests[] = $request;
    }

    public function shift(): Request
    {
        return array_shift($this->requests);
    }

    public function rejectAll(Exception $exception): void
    {
        while (!$this->isEmpty()) {
            $request = $this->shift();
            $request->reject($exception);
        }
    }

    public function isEmpty(): bool
    {
        return empty($this->requests);
    }
}
