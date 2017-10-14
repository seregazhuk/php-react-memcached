<?php

namespace seregazhuk\React\Memcached\Protocol;

use seregazhuk\React\Memcached\Protocol\Response\Factory as ResponseFactory;
use seregazhuk\React\Memcached\Protocol\Request\Factory as RequestFactory;

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

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @param ResponseFactory $responseFactory
     * @param RequestFactory $requestFactory
     */
    public function __construct(ResponseFactory $responseFactory, RequestFactory $requestFactory)
    {
        $this->responseFactory = $responseFactory;
        $this->requestFactory = $requestFactory;
    }

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
     * @param $args
     * @return string
     */
    public function makeRequest($command, $args)
    {
        return $this->requestFactory
            ->create($command, $args)
            ->getCommand();
    }

    /**
     * @param string $command
     * @param string $response
     * @return string
     */
    public function parseResponse($command, $response)
    {
        return $this->responseFactory
            ->makeByCommand($command, $response)
            ->parse();
    }
}
