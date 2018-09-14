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

final class Parser
{
    public const RESPONSE_STORED = 'STORED';
    public const RESPONSE_DELETED = 'DELETED';
    public const RESPONSE_NOT_FOUND = 'NOT_FOUND';
    public const RESPONSE_OK = 'OK';
    public const RESPONSE_VERSION = 'VERSION';

    private const RESPONSE_NOT_STORED = 'NOT_STORED';
    private const RESPONSE_END = 'END';
    private const RESPONSE_EXISTS = 'EXISTS';
    public const RESPONSE_TOUCHED = 'TOUCHED';
    private const RESPONSE_ERROR = 'ERROR';
    private const RESPONSE_RESET = 'RESET';

    private const RESPONSE_ENDS = [
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

    private const COMMAND_SET = 'set';

    public const COMMAND_SEPARATOR = "\r\n";

    private const STORAGE_COMMANDS = [
        self::COMMAND_SET,
        self::COMMAND_ADD,
        self::COMMAND_REPLACE,
    ];

    private const COMMAND_GET = 'get';

    private const COMMAND_VERSION = 'version';
    private const COMMAND_STATS = 'stats';
    private const COMMAND_TOUCH = 'touch';
    private const COMMAND_DELETE = 'delete';
    private const COMMAND_INCREMENT = 'incr';
    private const COMMAND_DECREMENT = 'decr';
    private const COMMAND_ADD = 'add';
    private const COMMAND_REPLACE = 'replace';
    private const COMMAND_VERBOSITY = 'verbosity';
    private const COMMAND_FLUSH_ALL = 'flushAll';

    private const COMMANDS = [
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
     * @return string[]
     */
    public function parseRawResponse(string $data = ''): array
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

            if (strpos($line, self::RESPONSE_VERSION) !== false) {
                $results[] = $line;
                $result = '';
            }

            $result .= self::COMMAND_SEPARATOR;
        }

        if (!empty($result) && $result !== self::COMMAND_SEPARATOR) {
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
    public function makeCommand($command, array $args)
    {
        return $this->createRequest($command, $args)->command();
    }

    /**
     * @param string $command
     * @param string $response
     * @return string
     * @throws WrongCommandException
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
     * @throws WrongCommandException
     */
    public function createResponse($command, $data)
    {
        switch ($command) {
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

        throw new WrongCommandException("Cannot parse response for command $command");
    }

    /**
     * @param string $command
     * @param array $args
     * @return Request
     * @throws WrongCommandException
     */
    public function createRequest($command, $args)
    {
        if (!in_array($command, self::COMMANDS)) {
            throw new WrongCommandException("Unknown command: $command");
        }

        if (in_array($command, self::STORAGE_COMMANDS)) {
            return new StorageRequest($command, ...$args);
        }

        return new SimpleRequest($command, $args);
    }
}
