<?php

namespace seregazhuk\React\Memcached;

use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;
use seregazhuk\React\Memcached\Protocol\Parser;
use seregazhuk\React\Memcached\Protocol\Response\Factory as ResponseFactory;
use seregazhuk\React\Memcached\Protocol\Request\Factory as RequestFactory;

class Factory
{
    /**
     * @var Connector
     */
	private $connector;

	/**
	 * @param LoopInterface $loop
	 */
	public function __construct(LoopInterface $loop)
	{
		$this->connector = new Connector($loop);
	}

	/**
	 * Creates a memcached client connected to a given connection string
	 *
	 * @param string $address Memcached server URI to connect to
	 * @return PromiseInterface resolves with Client or rejects with \Exception
	 */
	public function createClient($address)
	{
		$promise = $this
			->connector
			->connect($address)
			->then(function (ConnectionInterface $stream) {
                $parser = new Parser(new RequestFactory(), new ResponseFactory());
                return new Client($stream, $parser);
            });

		return $promise;
	}
}
