<?php

declare(strict_types = 1);

namespace PHPinnacle\NSQ;

abstract class Frame
{
    public const
        TYPE_RESPONSE = 0,
        TYPE_ERROR    = 1,
        TYPE_MESSAGE  = 2
    ;

    public function __construct(public int $type) {}

    public function response(): bool
    {
        return $this->type === self::TYPE_RESPONSE;
    }

    public function error(): bool
    {
        return $this->type === self::TYPE_ERROR;
    }

    public function message(): bool
    {
        return $this->type === self::TYPE_MESSAGE;
    }
}
