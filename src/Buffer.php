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

use PHPinnacle\Buffer\ByteBuffer;

final class Buffer extends ByteBuffer
{
    public function readUInt32LE(): int
    {
        return \unpack("V", $this->consume(4))[1];
    }
}
