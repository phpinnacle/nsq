<?php

declare(strict_types=1);

namespace PHPinnacle\NSQ\Frame;

use PHPinnacle\NSQ\Exception;
use PHPinnacle\NSQ\Frame;

final class Error extends Frame
{
    public function __construct(public string $data)
    {
        parent::__construct(self::TYPE_ERROR);
    }

    public function toException(): Exception\ServerException
    {
        return new Exception\ServerException($this->data);
    }
}
