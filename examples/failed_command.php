<?php

use seregazhuk\React\Memcached\Client;

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$client = new Client($loop);

$client
    ->touch('some_key', 12)
    ->then('var_dump', function(Exception $e){
        echo 'Error: ' . $e->getMessage();
    });

$loop->run();
