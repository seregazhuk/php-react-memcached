<?php

use seregazhuk\React\Memcached\Factory;

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$client = Factory::createClient($loop);

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

$loop->run();
