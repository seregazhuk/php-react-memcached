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
            echo "The value was added\n";
        });
        $client->get('name')->then(function($data){
            var_dump($data);
            echo "The value was retrieved\n";
        });
        $client->replace('name', 'new')->then(function($data){
            var_dump($data);
            echo "The value was replaced\n";
        });
        $client->get('name')->then(function($data){
            var_dump($data);
            echo "The value was retrieved\n";
        });
    },
    function(Exception $e){
        echo $e->getMessage(), "\n";
    });

$loop->run();
