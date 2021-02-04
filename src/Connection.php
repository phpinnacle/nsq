<?php
/**
 * This file is part of PHPinnacle/Amridge.
 *
 * (c) PHPinnacle Team <dev@phpinnacle.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace PHPinnacle\NSQ;

use Amp\Deferred;
use Amp\Socket\ConnectContext;
use Amp\Loop;
use Amp\Promise;
use function Amp\asyncCall, Amp\call, Amp\Socket\connect;

final class Connection
{
    private $parser;
    private $buffer;
    private $socket;
    private $callbacks = [];
    private $lastWrite = 0;

    public function __construct(private string $uri)
    {
        $this->parser = new Parser(new Buffer());
        $this->buffer = new Buffer;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function connect(int $timeout = 0, int $maxAttempts = 0, bool $noDelay = false): Promise
    {
        return call(function () use ($timeout, $maxAttempts, $noDelay) {
            $context = new ConnectContext();

            if ($maxAttempts > 0) {
                $context = $context->withMaxAttempts($maxAttempts);
            }

            if ($timeout > 0) {
                $context = $context->withConnectTimeout($timeout);
            }

            if ($noDelay) {
                $context = $context->withTcpNoDelay();
            }

            $this->socket = yield connect($this->uri, $context);

            asyncCall(function () {
                while (null !== $chunk = yield $this->socket->read()) {
                    $this->parser->append($chunk);

                    while ($response = $this->parser->parse()) {
                        foreach ($this->callbacks ?? [] as $i => $callback) {
                            asyncCall($callback, $response);

                            unset($this->callbacks[$i]);
                        }
                    }
                }

                $this->close();
            });
        });
    }

    public function command(Command $frame): Promise
    {
        $this->lastWrite = Loop::now(); // TODO: heartbeats

        return $this->socket->write($frame->pack($this->buffer));
    }

    public function response(): Promise
    {
        $deferred = new Deferred;

        $this->callbacks[] = function (Response $response) use ($deferred) {
            $deferred->resolve($response);
        };

        return $deferred->promise();
    }

    /**
     * @return void
     */
    public function close(): void
    {
        $this->callbacks = [];

        if ($this->socket !== null) {
            $this->socket->close();
        }
    }
}
