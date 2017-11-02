<?php

use seregazhuk\React\Memcached\Factory;

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$client = Factory::createClient($loop);
$client->set('ads', 'hello');
$client->get('ads')->then('var_dump', function(Exception $e){
    print_r($e->getMessage());
});
$client->version()->then('var_dump');
$loop->run();
