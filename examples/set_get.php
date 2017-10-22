<?php

use seregazhuk\React\Memcached\Exception\ConnectionClosedException;
use seregazhuk\React\Memcached\Factory;

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$client = Factory::createClient($loop);

//$client->connect('localhost:11211');

$client->set('example', 'Hello world')
    ->then(null, function(ConnectionClosedException $e) use ($client) {
        $client->connect('localhost:11211');
        die("AAA");
});

$client->get('example')->then(function ($data) {
    echo $data . PHP_EOL; // Hello world
});
// Close the connection when all requests are resolved

$loop->run();
