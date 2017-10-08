<?php

namespace seregazhuk\React\Memcached;

use LogicException;
use React\Promise\Deferred;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use React\Stream\DuplexStreamInterface;

class Client {
	private $stream;
	private $requests = [];

	const CRLF = "\r\n";

	public function __construct(DuplexStreamInterface $stream)
	{
		$stream->on('data', function ($chunk){
			$this->handleData($chunk);
		});

		$stream->on('close', [$this, 'close']);
		$this->stream = $stream;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 * @param int $exptime
	 * @param int $flags
	 * @return Promise|PromiseInterface
	 */
	public function set($key, $value, $exptime = 0, $flags = 0)
	{
		$query = $this->query(["set $key $flags $exptime " . strlen($value), $value]);

		return $this->executeQuery($query);
	}

	public function __call($name, $args)
	{
		$query = implode(' ', [$name, implode(' ', $args)]);

		return $this->executeQuery($this->query($query));
	}


	private function query($query)
	{
		$query = is_array($query) ? implode(self::CRLF, $query) : $query;
		return $query . self::CRLF;
	}

	/**
	 * @param string $query
	 * @return Promise|PromiseInterface
	 */
	protected function executeQuery($query)
	{
		$request = new Deferred();
		$promise = $request->promise();

		$this->stream->write($query);
		$this->requests[] = $request;

		return $promise;
	}

	protected function handleData($data)
	{
		var_dump($data);
		if (!$this->requests)
		{
			throw new LogicException('Unexpected reply received, no matching request found');
		}


		/* @var $request Deferred */
		$request = array_shift($this->requests);
		$request->resolve($this->parseResponse($data));
	}

	protected function parseResponse($response)
	{
		$parsed = preg_replace(["/VALUE \w+ \d+ \d+/", '/' . self::CRLF . "END" . '/'], "", $response);

		return trim($parsed);
	}
}
