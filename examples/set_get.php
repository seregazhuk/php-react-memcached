<?php

use seregazhuk\React\Memcached\Client;

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$client = new Client($loop);
$client->set('example', 'Hello world');

$client->get('example')->then(function ($data) {
    echo $data . PHP_EOL; // Hello world
});

// Close the connection when all requests are resolved
$client->end();

$loop->run();
