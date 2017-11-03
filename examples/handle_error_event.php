<?php

use seregazhuk\React\Memcached\Client;
use seregazhuk\React\Memcached\Exception\ConnectionClosedException;

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$client = new Client($loop);

$loop->addPeriodicTimer(1, function() use ($client) {
    $client->version()->then(function($version){
        echo 'Memcached version: ', $version, "\n";
    });
});

$client->on('error', function(ConnectionClosedException $e) use ($loop) {
    echo 'Error: ', $e->getMessage(), "\n";
    $loop->stop();
});

$client->on('close', function() {
    echo "Connection closed\n";
});

$loop->run();
