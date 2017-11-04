<?php

namespace seregazhuk\React\Memcached\tests\Functional;

use Clue\React\Block;
use PHPUnit\Framework\TestCase;
use React\EventLoop\LoopInterface;
use React\Promise\Promise;
use React\EventLoop\Factory as LoopFactory;

class WaitTestCase extends TestCase
{
    /**
     * @var LoopInterface
     */
    protected $loop;

    protected function setUp()
    {
        $this->loop = LoopFactory::create();
    }

    /**
     * @param Promise $promise
     * @return mixed
     * @throws \Exception
     */
    protected function waitForPromiseResolves(Promise $promise)
    {
        return Block\await($promise, $this->loop);
    }

    /**
     * @param Promise $promise
     * @return string
     */
    protected function waitForPromiseRejects(Promise $promise)
    {
        try {
            Block\await($promise, $this->loop);
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }
}
