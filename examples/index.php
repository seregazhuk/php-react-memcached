<?php

use seregazhuk\React\Memcached\Factory;

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$client = Factory::createClient($loop);
$client->set('a', 'hello');
$client->get('a')->then('var_dump', function(Exception $e){
    print_r($e->getMessage());
});
$loop->run();
