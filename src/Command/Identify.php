<?php

declare(strict_types=1);

namespace PHPinnacle\NSQ\Command;

use PHPinnacle\NSQ\Config;
use PHPinnacle\NSQ\Command;

class Identify extends Command
{
    private const PREFIX = '  V2';
    private const NAME = 'IDENTIFY';

    public function __construct(Config $config)
    {
        parent::__construct(self::PREFIX . self::NAME, data: $config->toString());
    }
}
