<?php

use seregazhuk\React\Memcached\Factory;

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$client = Factory::createClient($loop);

$loop->addPeriodicTimer(1, function () use ($client) {
    $client->version()->then('var_dump', function(Exception $exception) {
        var_dump($exception->getMessage());
    });
});
//$client->set('ads', 'hello');
//$client->get('ads')->then('var_dump', function(Exception $e){
//    print_r($e->getMessage());
//});
$loop->run();
