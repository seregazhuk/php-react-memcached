<?php

use seregazhuk\React\Memcached\Client;

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$client = new Client($loop);

$client->set('name', 'test')->then(function(){
    echo "The value was stored\n";
});
$client->end();
$client->get('name')->then(function($data){
    var_dump($data);
    echo "The value was retrieved\n";
}, function(Exception $e){
    echo $e->getMessage(), "\n";
});

$loop->run();
