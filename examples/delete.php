<?php

use seregazhuk\React\Memcached\Client;

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$client = new Client($loop);

$client->set('name', ['test'])->then(function($result){
    var_dump($result);
    echo "The value was stored\n";
});
$client->delete('name')->then(function($data){
    var_dump($data);
});

$loop->run();
