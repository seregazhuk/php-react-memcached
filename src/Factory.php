<?php

namespace seregazhuk\React\Memcached;

use React\EventLoop\LoopInterface;
use React\Promise;
use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;
use React\Socket\ConnectorInterface;
use InvalidArgumentException;

class Factory
{
	private $connector;

	/**
	 * @param LoopInterface $loop
	 * @param ConnectorInterface|null $connector [optional] Connector to use.
	 *     Should be `null` in order to use default Connector.
	 */
	public function __construct(LoopInterface $loop, ConnectorInterface $connector = null)
	{
		$this->connector = new Connector($loop);
	}

	/**
	 * Creates memcached client connected to a given connection string
	 *
	 * @param string $address Memcached server URI to connect to
	 * @return PromiseInterface resolves with Client or rejects with \Exception
	 */
	public function createClient($address)
	{
		try {
			$parts = $this->parseUrl($address);
		} catch (InvalidArgumentException $e) {
			return Promise\reject($e);
		}

		$promise = $this
			->connector
			->connect($parts['host'] . ':' . $parts['port'])
			->then(function (ConnectionInterface $stream) {
				return new Client($stream, new ProtocolParser());
		});

		return $promise;
	}
	/**
	 * @param string $target
	 * @return array with keys host, port, auth and db
	 * @throws InvalidArgumentException
	 */
	private function parseUrl($target)
	{
		$parts = parse_url($target);
		if (!isset($parts['port'])) {
			$parts['port'] = 112211;
		}
		unset($parts['scheme'], $parts['user'], $parts['pass'], $parts['path']);

		return $parts;
	}
}
