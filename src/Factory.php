<?php

namespace seregazhuk\React\Memcached;

use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;

class Factory
{
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
			->then(
			    function (ConnectionInterface $stream) {
				    return new Client($stream, new ProtocolParser());
                });

		return $promise;
	}
}
