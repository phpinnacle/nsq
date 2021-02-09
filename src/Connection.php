<?php

declare(strict_types = 1);

namespace PHPinnacle\NSQ;

use Amp\Deferred;
use Amp\Promise;
use Generator;
use function Amp\asyncCall, Amp\call;

final class Connection
{
    private $writeBuffer;
    private $readBuffer;
    private $parser;
    private $stream;
    private $deferreds = [];
    private $handlers = [];

    public function __construct(private string $uri)
    {
        $this->writeBuffer = new Buffer();
        $this->readBuffer  = new Buffer();
        $this->parser      = new Parser();
    }

    public function __destruct()
    {
        $this->close();
    }

    public function connect(): Promise
    {
        return call(function () {
            $this->stream = yield Stream\SocketStream::connect($this->uri);

            asyncCall(function () {
                while (null !== $chunk = yield $this->stream->read()) {
                    $this->readBuffer->append($chunk);

                    while ($response = $this->parser->parse($this->readBuffer)) {
                        yield from $this->handle($response);
                    }

                    if ($this->stream === null) {
                        break;
                    }
                }
            });
        });
    }

    public function command(string $name, array|string $params = [], string $data = null): Promise
    {
        $command = $params ? implode(' ', [$name, ...((array) $params)]) : $name;

        $this->writeBuffer->append($command.PHP_EOL);

        if ($data !== null) {
            $this->writeBuffer->appendUint32(\strlen($data));
            $this->writeBuffer->append($data);
        }

        return $this->stream->write($this->writeBuffer->flush());
    }

    public function response(): Promise
    {
        $this->deferreds[] = $deferred = new Deferred;

        return $deferred->promise();
    }

    public function listen(callable $handler): void
    {
        $this->handlers[] = $handler;
    }

    public function useSnappy(): Promise
    {
        $this->stream = new Stream\SnappyStream($this->stream);

        return $this->response();
    }

    public function useDeflate(): Promise
    {
        $this->stream = new Stream\SnappyStream($this->stream);

        return $this->response();
    }

    public function close(): void
    {
        $this->deferreds = [];

        if ($this->stream !== null) {
            $this->stream->close();

            $this->stream = null;
        }
    }

    private function handle(Frame $frame): Generator
    {
        switch (true) {
            case $frame instanceof Frame\Response:
                if ($frame->heartbeat()) {
                    yield $this->command(Commands::NOP);

                    return;
                }

                foreach ($this->deferreds ?? [] as $i => $deferred) {
                    unset($this->deferreds[$i]);

                    $deferred->resolve($frame);
                }

                break;
            case $frame instanceof Frame\Error:
                foreach ($this->deferreds ?? [] as $i => $deferred) {
                    unset($this->deferreds[$i]);

                    $deferred->fail($frame->toException());
                }

                break;
            case $frame instanceof Frame\Message:
                foreach ($this->handlers as $handler) {
                    asyncCall($handler, $frame);
                }

                break;
        }
    }
}
