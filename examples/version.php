<?php

use seregazhuk\React\Memcached\Factory;
use seregazhuk\React\Memcached\Client;

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$factory = new Factory($loop);

$factory->createClient('localhost:11211')->then(
	function (Client $client) {
        $client->version()->then(function($result){
            echo "Memcached version: $result\n";
        });
        $client->verbosity(2)->then(function($result){
            var_dump($result); die();
        });
	},
	function(Exception $e){
        echo 'Error connecting to server: ' . $e->getMessage();
	});

$loop->run();
