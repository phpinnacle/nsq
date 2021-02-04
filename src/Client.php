<?php

declare(strict_types=1);

namespace PHPinnacle\NSQ;

use Amp\Promise;
use function Amp\call;

class Client
{
    public function __construct(private Connection $connection) {}

    public static function connect(string $uri, Config $config = null): Promise
    {
        return call(function () use ($uri, $config) {
            $connection = new Connection($uri);

            yield $connection->connect();
            yield $connection->command(new Command\Identify($config ?? new Config()));

            $response = yield $connection->response();

            // TODO: upgrade TLS, Snappy, etc
            var_dump($response);

            return new self($connection);
        });
    }

    public function publish(string $topic, string $body): Promise
    {
        return call(function () use ($topic, $body) {
            yield $this->connection->command(new Command\Publish($topic, $body));

            $response = yield $this->connection->response();

            var_dump($response);
        });
    }
}
