<?php

declare(strict_types=1);

namespace PHPinnacle\NSQ;

use Amp\Promise;
use function Amp\call;

final class Message
{
    private $processed = false;

    public function __construct(
        private Client $client,
        public string $id,
        public string $body,
        public int $timestamp,
        public int $attempts
    ) {}

    public static function compose(Client $client, Frame\Message $message): self
    {
        return new self(
            $client,
            $message->id,
            $message->body,
            $message->timestamp,
            $message->attempts
        );
    }

    public function finish(): Promise
    {
        return call(function () {
            if ($this->processed) {
                throw Exception\MessageException::processed($this);
            }

            yield $this->client->finish($this->id);

            $this->processed = true;
        });
    }

    public function requeue(int $timeout): Promise
    {
        return call(function () use ($timeout) {
            if ($this->processed) {
                throw Exception\MessageException::processed($this);
            }

            yield $this->client->requeue($this->id, $timeout);

            $this->processed = true;
        });
    }

    public function touch(): Promise
    {
        return call(function () {
            if ($this->processed) {
                throw Exception\MessageException::processed($this);
            }

            yield $this->client->touch($this->id);

            $this->processed = true;
        });
    }
}
