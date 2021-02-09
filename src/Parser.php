<?php

declare(strict_types = 1);

namespace PHPinnacle\NSQ;

class Parser
{
    private const SIZE = 4;
    private const MESSAGE_HEADER_SIZE =
        8 +  // timestamp
        2 +  // attempts
        16 + // ID
        4;   // Frame type

    public function parse(Buffer $buffer): ?Frame
    {
        if ($buffer->size() < self::SIZE) {
            return null;
        }

        $size = $buffer->readInt32();

        if ($buffer->size() < $size) {
            return null;
        }

        $buffer->discard(self::SIZE);

        $type = $buffer->consumeInt32();

        return match($type) {
            Frame::TYPE_RESPONSE => new Frame\Response($buffer->consumeData($size)),
            Frame::TYPE_ERROR => new Frame\Error($buffer->consumeData($size)),
            Frame::TYPE_MESSAGE => new Frame\Message(
                timestamp: $buffer->consumeTimestamp(),
                attempts: $buffer->consumeAttempts(),
                id: $buffer->consumeMessageID(),
                body: $buffer->consume($size - self::MESSAGE_HEADER_SIZE),
            ),
        };
    }
}
