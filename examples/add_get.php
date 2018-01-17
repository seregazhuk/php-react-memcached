<?php

use seregazhuk\React\Memcached\Factory;

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$client = Factory::createClient($loop);

$client->add('name', ['test'])->then(function ($result) {
    var_dump($result);
    echo "The value was added\n";
});
$client->get('name')->then(function ($data) {
    var_dump($data);
    echo "The value was retrieved\n";
});

$loop->run();
