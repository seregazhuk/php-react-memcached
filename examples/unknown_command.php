<?php

use seregazhuk\React\Memcached\Factory;

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$client = Factory::createClient($loop);

$client
    ->someCommand()
    ->then('var_dump', function (Exception $e) {
        echo $e->getMessage();
    });

$loop->run();
