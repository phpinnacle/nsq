<?php

declare(strict_types=1);

use Amp\Loop;
use PHPinnacle\NSQ\Client;

require __DIR__ . '/../vendor/autoload.php';

Loop::run(function () {
    /** @var Client $client */
    $client = yield Client::connect("tcp://localhost:4150");

    yield $client->publish('test', 'data');
});
