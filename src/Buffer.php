<?php

declare(strict_types = 1);

namespace PHPinnacle\NSQ;

use PHPinnacle\Buffer\ByteBuffer;

final class Buffer extends ByteBuffer
{
    public function readUInt32LE(): int
    {
        return \unpack("V", $this->read(4))[1];
    }

    public function consumeData($size): string
    {
        return $size > 4 ? $this->consume($size - 4) : '';
    }

    public function consumeTimestamp(): int
    {
        return $this->consumeUInt64();
    }

    public function consumeAttempts(): int
    {
        return $this->consumeUInt16();
    }

    public function consumeMessageID(): string
    {
        return $this->consume(16);
    }
}
