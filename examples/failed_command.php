<?php

use seregazhuk\React\Memcached\Factory;

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$client = Factory::createClient($loop);

$client
    ->touch('some_key', 12)
    ->then('var_dump', function(Exception $e){
        echo 'Error: ' . $e->getMessage();
    });

$loop->run();
