<?php

use seregazhuk\React\Memcached\Client;

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$client = new Client($loop);

$client
    ->someCommand()
    ->then('var_dump', function(Exception $e){
        echo $e->getMessage();
    });

$loop->run();
