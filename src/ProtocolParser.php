<?php

namespace seregazhuk\React\Memcached;

class ProtocolParser
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

        return $results;
    }

    /**
     * @param string $command
     * @param $args
     * @return string
     */
    public function makeRequest($command, $args)
    {
        if(in_array($command, self::STORAGE_COMMANDS)) {
            return $this->makeStorageCommand($command, ...$args);
        }

        return $this->makeRetrievalCommand($command, $args);
    }

    /**
     * @param string $command
     * @param string $key
     * @param mixed $value
     * @param int $flags
     * @param int $expiration
     * @return string
     */
    private function makeStorageCommand($command, $key, $value, $flags = 0, $expiration = 0)
    {
        $command = implode(' ', [$command, $key, $flags, $expiration, strlen($value)]);

        return $command . self::COMMAND_SEPARATOR . $value . self::COMMAND_SEPARATOR;
    }
    
    private function makeRetrievalCommand($command, $args)
    {
        return $command . ' ' . implode(' ' , $args) . self::COMMAND_SEPARATOR;
    }

    /**
     * @param string $response
     * @return bool
     */
    private function parseWriteResponse($response)
    {
        return $response === self::RESPONSE_STORED;
    }

    /**
     * @param string $response
     * @return string|null
     */
    private function parseReadResponse($response)
    {
        $regExp = '/VALUE \w+ \d+ \d+' . self::COMMAND_SEPARATOR . '(.*)' . self::COMMAND_SEPARATOR . 'END/';
        preg_match($regExp, $response, $match);

        return isset($match[1]) ? $match[1] : null;
    }

    /**
     * @param string $command
     * @param string $response
     * @return string
     */
    public function parseResponse($command, $response)
    {
        if(in_array($command, self::RETRIEVAL_COMMANDS)) {
            return $this->parseReadResponse($response);
        }

        if(in_array($command, self::STORAGE_COMMANDS)) {
            return $this->parseWriteResponse($response);
        }

        if($command == self::COMMAND_VERSION)  {
            return $this->parseVersionResponse($response);
        }
    }

    /**
     * @param string $response
     * @return string
     */
    private function parseVersionResponse($response)
    {
        return trim(str_replace(self::RESPONSE_VERSION, '', $response));
    }
}
