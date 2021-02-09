<?php

declare(strict_types=1);

namespace PHPinnacle\NSQ;

use Amp\Promise;
use function Amp\asyncCall;
use function Amp\call;

final class Client
{
    public function __construct(private Connection $connection, private Config\ServerConfig $config) {}

    public static function connect(string $uri, Config\ClientConfig $config = null): Promise
    {
        $config = $config ?? new Config\ClientConfig();

        return call(function () use ($uri, $config) {
            $connection = new Connection($uri);

            yield $connection->connect();
            yield $connection->command(Commands::IDENTIFY, data: $config->toString());

            $response = yield $connection->response();

            $self = new self($connection, Config\ServerConfig::fromResponse($response));

            yield $self->configure();

            return $self;
        });
    }

    public function publish(string $topic, string|array $body): Promise
    {
        $command = Commands::PUB;

        if (\is_array($body)) {
            $buffer = new Buffer();
            $buffer->appendUint32(\count($body));

            foreach ($body as $item) {
                $buffer->appendUint32(\strlen($item));
                $buffer->append($body);
            }

            $command = Commands::MPUB;
            $body    = $buffer->bytes();
        }

        return $this->command($command, $topic, $body);
    }

    public function delay(string $topic, string $body, int $delay): Promise
    {
        return $this->command(Commands::DPUB, [$topic, $delay], $body);
    }

    public function listen(string $topic, string $channel, callable $handler): Promise
    {
        return call(function () use ($topic, $channel, $handler) {
            yield $this->command(Commands::SUB, [$topic, $channel]);

            $this->connection->listen(function (Frame\Message $frame) use ($handler) {
                asyncCall($handler, Message::compose($this, $frame));
            });

            yield $this->command(Commands::RDY, (string) $this->config->maxRdyCount);
        });
    }

    public function finish(string $id): Promise
    {
        return $this->connection->command(Commands::FIN, $id);
    }

    public function requeue(string $id, int $timeout = 0)
    {
        return $this->connection->command(Commands::REQ, [$id, $timeout]);
    }

    public function touch(string $id)
    {
        return $this->connection->command(Commands::TOUCH, $id);
    }

    private function command(string $name, array|string $params = [], string $data = null): Promise
    {
        return call(function () use ($name, $params, $data) {
            yield $this->connection->command($name, $params, $data);

            return $this->connection->response();
        });
    }

    public function disconnect(): Promise
    {
        return call(function () {
//            yield $this->command(Commands::CLS);

            $this->connection->close();
        });
    }

    private function configure(): Promise
    {
        return call(function () {
            if ($this->config->snappy) {
                yield $this->connection->useSnappy();
            }

            if ($this->config->deflate) {
                yield $this->connection->useSnappy();
            }
        });
    }
}
