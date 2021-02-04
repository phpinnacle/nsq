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

final class Parser
{
    public function __construct(private Buffer $buffer) {}

    public function append(string $chunk): void
    {
        $this->buffer->append($chunk);
    }

    public function parse(): ?Response
    {
        if ($this->buffer->size() < 4) {
            return null;
        }

        $size  = $this->buffer->readInt32();

        if ($this->buffer->size() < $size) {
            return null;
        }

        $this->buffer->discard(4);

        $type  = $this->buffer->consumeInt32();
        $data  = $size > 4 ? $this->buffer->consume($size - 4) : '';

        return new Response($type, $size, $data);
    }
}
