<?php

namespace seregazhuk\React\Memcached\Protocol;

use seregazhuk\React\Memcached\Exception\FailedCommandException;
use seregazhuk\React\Memcached\Exception\WrongCommandException;
use seregazhuk\React\Memcached\Protocol\Request\Request;
use seregazhuk\React\Memcached\Protocol\Request\SimpleRequest;
use seregazhuk\React\Memcached\Protocol\Request\StorageRequest;
use seregazhuk\React\Memcached\Protocol\Response\DeleteResponse;
use seregazhuk\React\Memcached\Protocol\Response\OkResponse;
use seregazhuk\React\Memcached\Protocol\Response\ReadResponse;
use seregazhuk\React\Memcached\Protocol\Response\Response;
use seregazhuk\React\Memcached\Protocol\Response\StatsResponse;
use seregazhuk\React\Memcached\Protocol\Response\TouchResponse;
use seregazhuk\React\Memcached\Protocol\Response\ValueResponse;
use seregazhuk\React\Memcached\Protocol\Response\VersionResponse;
use seregazhuk\React\Memcached\Protocol\Response\WriteResponse;

class Parser
{
    const RESPONSE_END = 'END';
    const RESPONSE_STORED = 'STORED';
    const RESPONSE_NOT_STORED = 'NOT_STORED';
    const RESPONSE_DELETED = 'DELETED';
    const RESPONSE_NOT_FOUND = 'NOT_FOUND';
    const RESPONSE_OK = 'OK';
    const RESPONSE_EXISTS = 'EXISTS';
    const RESPONSE_ERROR = 'ERROR';
    const RESPONSE_RESET = 'RESET';
    const RESPONSE_VERSION = 'VERSION';
    const RESPONSE_VALUE = 'VALUE';
    const RESPONSE_TOUCHED = 'TOUCHED';

    CONST RESPONSE_ENDS = [
        self::RESPONSE_END,
        self::RESPONSE_DELETED,
        self::RESPONSE_NOT_FOUND,
        self::RESPONSE_OK,
        self::RESPONSE_EXISTS,
        self::RESPONSE_ERROR,
        self::RESPONSE_RESET,
        self::RESPONSE_STORED,
        self::RESPONSE_NOT_STORED,
        self::RESPONSE_TOUCHED,
    ];

    const COMMAND_SET = 'set';

    const COMMAND_SEPARATOR = "\r\n";

    const STORAGE_COMMANDS = [
        self::COMMAND_SET,
        self::COMMAND_ADD,
        self::COMMAND_REPLACE,
    ];

    const COMMAND_GET = 'get';

    const RETRIEVAL_COMMANDS = [
        self::COMMAND_GET,
    ];

    const WRITE_RESPONSE_ENDS = [
        self::RESPONSE_STORED,
        self::RESPONSE_NOT_STORED,
        self::RESPONSE_EXISTS,
        self::RESPONSE_NOT_FOUND,
    ];

    const COMMAND_VERSION = 'version';
    const COMMAND_STATS = 'stats';
    const COMMAND_TOUCH = 'touch';
    const COMMAND_DELETE = 'delete';
    const COMMAND_INCREMENT = 'incr';
    const COMMAND_DECREMENT = 'decr';
    const COMMAND_ADD = 'add';
    const COMMAND_REPLACE = 'replace';
    const COMMAND_VERBOSITY = 'verbosity';
    const COMMAND_FLUSH_ALL = 'flushAll';

    const COMMANDS = [
        self::COMMAND_ADD,
        self::COMMAND_DECREMENT,
        self::COMMAND_DELETE,
        self::COMMAND_FLUSH_ALL,
        self::COMMAND_GET,
        self::COMMAND_INCREMENT,
        self::COMMAND_REPLACE,
        self::COMMAND_SET,
        self::COMMAND_STATS,
        self::COMMAND_TOUCH,
        self::COMMAND_VERBOSITY,
        self::COMMAND_VERSION,
    ];

    /**
     * @param string $data
     * @return array
     */
    public function parseRawResponse($data = '')
    {
        $data = substr($data, 0, strlen($data) - strlen(self::COMMAND_SEPARATOR));
        $lines = explode(self::COMMAND_SEPARATOR, $data);

        $results = [];
        $result = '';

        foreach ($lines as $line) {
            $result .= $line;

            if (in_array($line, self::RESPONSE_ENDS)) {
                $results[] = $result;
                $result = '';
            }

            if(strpos($line, self::RESPONSE_VERSION) !== false) {
                $results[] = $line;
                $result = '';
            }

            $result .= self::COMMAND_SEPARATOR;
        }

        if(!empty($result) && $result !== self::COMMAND_SEPARATOR) {
            $results[] = $result;
        }

        return $results;
    }

    /**
     * @param string $command
     * @param array $args
     * @return string
     * @throws WrongCommandException
     */
    public function makeRequest($command, array $args)
    {
        return $this->createRequest($command, $args)->getCommand();
    }

    /**
     * @param string $command
     * @param string $response
     * @return string
     * @throws FailedCommandException
     */
    public function parseResponse($command, $response)
    {
        return $this->createResponse($command, $response)->parse();
    }

    /**
     * @param string $command
     * @param string $data
     * @return Response
     */
    public function createResponse($command, $data)
    {
        switch($command) {
            case self::COMMAND_GET:
                return new ReadResponse($data);
            case self::COMMAND_SET:
            case self::COMMAND_ADD:
            case self::COMMAND_REPLACE:
                return new WriteResponse($data);
            case self::COMMAND_VERSION:
                return new VersionResponse($data);
            case self::COMMAND_STATS:
                return new StatsResponse($data);
            case self::COMMAND_TOUCH:
                return new TouchResponse($data);
            case self::COMMAND_DELETE:
                return new DeleteResponse($data);
            case self::COMMAND_VERBOSITY:
            case self::COMMAND_FLUSH_ALL:
                return new OkResponse($data);
            case self::COMMAND_INCREMENT:
            case self::COMMAND_DECREMENT:
                return new ValueResponse($data);
        }
    }

    /**
     * @param string $command
     * @param array $args
     * @return Request
     * @throws WrongCommandException
     */
    public function createRequest($command, $args)
    {
        if(!in_array($command, self::COMMANDS)) {
            throw new WrongCommandException("Unknown command: $command");
        }

        if(in_array($command, self::STORAGE_COMMANDS)) {
            return new StorageRequest($command, ...$args);
        }

        return new SimpleRequest($command, $args);
    }
}
