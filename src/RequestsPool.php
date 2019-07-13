<?php declare(strict_types=1);

namespace seregazhuk\React\Memcached;

use seregazhuk\React\Memcached\Exception\Exception;

final class RequestsPool
{
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
        while (!empty($this->requests)) {
            $request = array_shift($this->requests);
            $request->reject($exception);
        }
    }

    public function isEmpty(): bool
    {
        return empty($this->requests);
    }
}
