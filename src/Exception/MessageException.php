<?php

declare(strict_types=1);

namespace PHPinnacle\NSQ\Exception;

use PHPinnacle\NSQ\Message;

final class MessageException extends NSQException
{
    public static function processed(Message $message): self
    {
        return new self(sprintf('Message "%s" already processed.', $message->id));
    }
}
