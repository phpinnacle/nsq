<?php

declare(strict_types=1);

namespace PHPinnacle\NSQ\Frame;

use PHPinnacle\NSQ\Frame;

final class Response extends Frame
{
    private const
        DATA_OK = 'OK',
        DATA_HEARTBEAT = '_heartbeat_'
    ;

    public function __construct(public string $data)
    {
        parent::__construct(self::TYPE_RESPONSE);
    }

    public function ok(): bool
    {
        return $this->data === self::DATA_OK;
    }

    public function heartbeat(): bool
    {
        return $this->data === self::DATA_HEARTBEAT;
    }
}
