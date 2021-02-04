<?php

declare(strict_types=1);

namespace PHPinnacle\NSQ\Command;

use PHPinnacle\NSQ\Command;

class Publish extends Command
{
    private const NAME = 'PUB';

    public function __construct(string $topic, string $data)
    {
        parent::__construct(self::NAME, $topic, $data);
    }
}
