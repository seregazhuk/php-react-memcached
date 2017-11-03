<?php

use seregazhuk\React\Memcached\Client;

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$client = new Client($loop);
$client->version()->then(function($result){
    echo "Memcached version: $result\n";
});
$client->verbosity(2)->then(function($result){
    var_dump($result); die();
});

$loop->run();
