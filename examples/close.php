<?php

use seregazhuk\React\Memcached\Factory;
use seregazhuk\React\Memcached\Client;

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$factory = new Factory($loop);

$factory->createClient('localhost:11211')->then(
    function (Client $client) {
        $client->set('name', 'test')->then(function(){
            echo "The value was stored\n";
        }, function(Exception $e){
            echo 'set: ', $e->getMessage(), "\n";
        });
        $client->close();
        $client->get('name')->then(function($data){
            var_dump($data);
            echo "The value was retrieved\n";
        }, function(Exception $e){
            echo 'get: ', $e->getMessage(), "\n";
        });
    },
    function(Exception $e){
        echo $e->getMessage(), "\n";
    });

$loop->run();
