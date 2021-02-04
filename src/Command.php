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

abstract class Command
{
    public function __construct(public string $name, public array|string $params = [], public ?string $data = null) {}

    public function pack(Buffer $buffer) :string
    {
        $command = $this->params ? implode(' ', [$this->name, ...((array) $this->params)]) : $this->name;

        $buffer = $buffer->append($command.PHP_EOL);

        if ($this->data !== null) {
            $buffer->appendUint32(\strlen($this->data));
            $buffer->append($this->data);
        }

        return $buffer->flush();
    }
}
