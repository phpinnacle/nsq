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

class Response
{
    const
        TYPE_RESPONSE = 0,
        TYPE_ERROR    = 1,
        TYPE_MESSAGE  = 2
    ;

    public function __construct(public int $type, public int $size, public string $data) {}
}
