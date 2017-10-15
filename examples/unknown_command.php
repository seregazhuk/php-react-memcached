<?php

use seregazhuk\React\Memcached\Factory;
use seregazhuk\React\Memcached\Client;
use seregazhuk\React\Memcached\Protocol\Exception\Exception;

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$factory = new Factory($loop);

$factory->createClient('localhost:11211')->then(
    function (Client $client) {
        $client->unknown()->then('var_dump', function(Exception $e){
            echo $e->getMessage();
        });
    },
    function(Exception $e){
        echo 'Error connecting to server: ' . $e->getMessage();
    });

$loop->run();
