<?php

use seregazhuk\React\Memcached\Factory;
use seregazhuk\React\Memcached\Client;

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$factory = new Factory($loop);

$factory->createClient('localhost:11211')->then(
	function (Client $client) {
        $client->stats()->then(function($result){
            print_r($result);
        });
	},
	function(Exception $e){
        echo 'Error connecting to server: ' . $e->getMessage();
	});

$loop->run();
