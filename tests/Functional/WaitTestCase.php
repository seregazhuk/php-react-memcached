<?php

namespace seregazhuk\React\Memcached\tests\Functional;

use Clue\React\Block;
use PHPUnit\Framework\TestCase;
use React\EventLoop\LoopInterface;
use React\EventLoop\Factory as LoopFactory;
use React\Promise\PromiseInterface;

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
     * @param PromiseInterface $promise
     * @return mixed
     * @throws \Exception
     */
    public function waitForPromiseResolves(PromiseInterface $promise)
    {
        return Block\await($promise, $this->loop);
    }

    /**
     * @param PromiseInterface $promise
     * @return string
     */
    protected function waitForPromiseRejects(PromiseInterface $promise)
    {
        try {
            Block\await($promise, $this->loop);
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

        $this->fail('Promise was resolved');
    }

    /**
     * @param mixed $value
     * @param PromiseInterface $promise
     * @throws \Exception
     */
    public function assertPromiseResolvesWith($value, PromiseInterface $promise)
    {
        $this->assertEquals($value, $this->waitForPromiseResolves($promise));
    }
}
