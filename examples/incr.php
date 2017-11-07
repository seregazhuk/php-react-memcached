<?php

use seregazhuk\React\Memcached\Factory;

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$client = Factory::createClient($loop);

//$client->set('var', 9)->then(function($result){
//    var_dump($result);
//    echo "The value was stored\n";
//});
$client->set('var', 2);
$client->incr('var', 2)->then(function($data){
    var_dump($data);
});


$loop->run();
