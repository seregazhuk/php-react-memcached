<?php

namespace seregazhuk\React\Memcached;

use InvalidArgumentException;
use React\Promise\Deferred;
use React\Stream\DuplexStreamInterface;
use RuntimeException;

class Client
{
    private $stream;
    private $requests = [];
    private $ending   = FALSE;
    private $closed   = FALSE;

    public function __construct(DuplexStreamInterface $stream)
    {
        $stream->on('data', function ($chunk) {
            var_dump($chunk);
            var_dump('data');
            die();
        });

        $stream->on('close', [$this, 'close']);
        $this->stream = $stream;
    }

    public function __call($name, $args)
    {
        $request = new Deferred();
        $promise = $request->promise();
        $name = strtolower($name);

        if ($this->ending) {
            $request->reject(new RuntimeException('Connection closed'));
        } else {
            $query = implode(' ', [$name, implode(' ', $args)]);
            var_dump($this->query($query)); die();
            $this->stream->write($this->query($query));
            $this->requests [] = $request;
        }

        return $promise;
    }

    public function end()
    {
        $this->ending = TRUE;
        if (!$this->requests) {
            $this->close();
        }
    }

    public function close()
    {
        if ($this->closed) {
            return;
        }
        $this->ending = TRUE;
        $this->closed = TRUE;
        $this->stream->close();
        $this->emit('close');
        // reject all remaining requests in the queue
        while ($this->requests) {
            $request = array_shift($this->requests);
            /* @var $request Request */
            $request->reject(new RuntimeException('Connection closing'));
        }
    }


    private function query($query)
    {
        $query = is_array($query) ? implode("\r\n", $query) : $query;

        return $query;
    }

    private function parseLine()
    {
        $line = fgets($this->socket);
        print $line.'<br>';
        $this->reply = substr($line, 0, strlen($line) - 2);

        $words = explode(' ', $this->reply);

        $result = isset($this->replies[$words[0]]) ? $this->replies[$words[0]] : $words;

        if (is_null($result)) {
            throw new \Exception($this->reply);
        }

        if ($result[0] == 'VALUE') {
            $value = fread($this->socket, $result[3] + 2);

            return $value;
        }

        return $result;
    }
}
