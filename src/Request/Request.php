<?php

namespace seregazhuk\React\Memcached\Request;

use Exception;
use React\Promise\Deferred;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

final class Request
{
    /**
     * @var Deferred
     */
    private $deferred;

    /**
     * @var string
     */
    private $command;

    /**
     * @param string $command
     */
    public function __construct(string $command)
    {
        $this->deferred = new Deferred();
        $this->command = $command;
    }

    /**
     * @return string
     */
    public function command(): string
    {
        return $this->command;
    }

    /**
     * @return Promise|PromiseInterface
     */
    public function promise()
    {
        return $this->deferred->promise();
    }

    /**
     * @param mixed $value
     */
    public function resolve($value): void
    {
        $this->deferred->resolve($value);
    }

    /**
     * @param Exception $exception
     */
    public function reject(Exception $exception): void
    {
        $this->deferred->reject($exception);
    }
}
