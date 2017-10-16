<?php

use seregazhuk\React\Memcached\Factory;
use seregazhuk\React\Memcached\Client;

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$factory = new Factory($loop);

$factory->createClient('localhost:11211')->then(
    function (Client $client) {
        $client->set('name', ['test'])->then(function($result){
            var_dump($result);
            echo "The value was stored\n";
        });
        $client->close();
        $client->get('name')->then(function($data){
            var_dump($data);
            echo "The value was retrieved\n";
        }, function($e){
            print_r($e->getMessage()); die();
        });
    },
    function(Exception $e){
        echo $e->getMessage(), "\n";
    });

$loop->run();
