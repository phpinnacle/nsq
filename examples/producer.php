<?php

declare(strict_types=1);

use Amp\Loop;
use PHPinnacle\NSQ\Client;
use PHPinnacle\NSQ\Config;

require __DIR__ . '/../vendor/autoload.php';

Loop::run(function () {
    /** @var Client $client */
    $client = yield Client::connect("tcp://localhost:4150", new Config\ClientConfig(snappy: true));

    yield $client->publish('test', 'data');
    yield $client->disconnect();
});
