<?php

declare(strict_types=1);

use Amp\Loop;
use PHPinnacle\NSQ\Client;
use PHPinnacle\NSQ\Message;

require __DIR__ . '/../vendor/autoload.php';

Loop::run(function () {
    /** @var Client $client */
    $client = yield Client::connect("tcp://localhost:4150");

    yield $client->listen('test', 'test', function (Message $message) {
        echo 'ID: ', $message->id, ' BODY: ', $message->body, \PHP_EOL;

        yield $message->finish();
    });
});
