<?php

declare(strict_types = 1);

namespace PHPinnacle\NSQ;

use Amp\Promise;

interface Stream
{
    public function read(): Promise;
    public function write(string $data): Promise;
    public function close(): void;
}
