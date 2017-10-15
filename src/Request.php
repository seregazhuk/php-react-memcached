<?php

namespace seregazhuk\React\Memcached;

use Exception;
use React\Promise\Deferred;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

class Request
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
    public function __construct($command)
    {
        $this->deferred = new Deferred();
        $this->command = $command;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @return Promise|PromiseInterface
     */
    public function getPromise()
    {
        return $this->deferred->promise();
    }

    /**
     * @param mixed $value
     */
    public function resolve($value)
    {
        $this->deferred->resolve($value);
    }

    /**
     * @param Exception $exception
     */
    public function reject(Exception $exception)
    {
        $this->deferred->reject($exception);
    }
}
