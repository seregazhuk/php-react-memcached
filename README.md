# Memcached ReactPHP Client
Asynchronous Memcached PHP Client for [ReactPHP](http://reactphp.org/) ecosystem.

[![Build Status](https://travis-ci.org/seregazhuk/php-memcached-react.svg?branch=master)](https://travis-ci.org/seregazhuk/php-memcached-react)

## Installation

### Dependencies
Library requires PHP 5.6.0 or above.

The recommended way to install this library is via [Composer](https://getcomposer.org). 
[New to Composer?](https://getcomposer.org/doc/00-intro.md)

```
composer require ?
```

## Quick Start

```php
$loop = React\EventLoop\Factory::create();
$factory = new Factory($loop);

$factory
    ->createClient('localhost:11211')
    ->then(function (Client $client) {
        $client->set('example', 'Hello world');

        $client->get('example')->then(function ($data) {
            echo $data . PHP_EOL; // Hello world
        });

        // Close the connection when all requests are resolved
        $client->end();
});

$loop->run();
```
See [other examples](https://github.com/seregazhuk/php-memcached-react/tree/master/examples).

## Connection

You can connect to server and create a client via the factory. It requires an instance of the `EventLoopInterface`:

```php
$loop = React\EventLoop\Factory::create();
$factory = new Factory($loop);
```

Then to create a client call `createClient()` method and pass a connection string:
```php
$factory->createClient('localhost:11211'')->then(
    function (Client $client) {
        // client connected
    },
    function (Exception $e) {
        // an error occurred while trying to connect 
    }
);
```

This method returns a promise. If connection was established the promise resolves with an instance of the `Client`. If 
something went wrong and connection wasn't established the promise will be rejected.

## Client

For each memcached command a client has a method. All commands are executed asynchronously. The client stored pending 
requests and once it receives the response from the server, it starts resolving these requests. That means that each 
command returns a promise. When the server executed a command and returns a response, the promise will be resolved 
with this response. If there was an error, the promise will be rejected. 

## Retrieval Commands

### Get
Get value from key:

```php
$client
    ->get('some-key')
    ->then(function ($data) {
        echo "Retreived value: " . $data . PHP_EOL; 
    });
```

## Storage Commands

### Set
Store key/value pair in Memcached:

```php
$client
    ->set('some-key', 'my-data')
    ->then(function ($result) {
        if($result) {
            echo "Value was stored" . PHP_EOL;
        } 
    });
```

### Add
Store key/value pair in Memcached, but only if the server **doesn’t** already hold data for this key:

```php
$client->add('name', 'test')
    ->then(function($result) {
        if($result) {
            echo "The value was added\n";
        }
    });
```

### Replace
