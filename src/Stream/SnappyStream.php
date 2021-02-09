<?php

declare(strict_types=1);

namespace PHPinnacle\NSQ\Stream;

use Amp\Promise;
use PHPinnacle\NSQ\Buffer;
use PHPinnacle\NSQ\Exception;
use PHPinnacle\NSQ\Stream;
use function Amp\call;

class SnappyStream implements Stream
{
    private const IDENTIFIER = [0xff, 0x06, 0x00, 0x00, 0x73, 0x4e, 0x61, 0x50, 0x70, 0x59];

    private const
        SIZE_HEADER     = 4,
        SIZE_CHECKSUM   = 4,
        SIZE_IDENTIFIER = 6,
        SIZE_CHUNK      = 65536
    ;

    private const
        TYPE_IDENTIFIER   = 0xff,
        TYPE_COMPRESSED   = 0x00,
        TYPE_UNCOMPRESSED = 0x01,
        TYPE_PADDING      = 0xfe
    ;

    private $buffer;

    public function __construct(private Stream $stream)
    {
        if (!\function_exists('snappy_uncompress')) {
            throw Exception\SnappyException::notInstalled();
        }

        $this->buffer = new Buffer();
    }

    public function read(): Promise
    {
        return call(function () {
            if ($this->buffer->size() < 4) {
                $this->buffer->append(yield $this->stream->read());
            }

            $type = $this->buffer->readUInt32LE();

            $size = $type >> 8;
            $type &= 0xff;

            while ($this->buffer->size() < $size) {
                $this->buffer->append(yield $this->stream->read());
            }

            $this->buffer->discard(self::SIZE_HEADER);

            switch ($type) {
                case self::TYPE_IDENTIFIER:
                    $this->buffer->discard(self::SIZE_IDENTIFIER);

                    return $this->read();
                case self::TYPE_COMPRESSED:
                    $this->buffer->discard(self::SIZE_CHECKSUM);

                    var_dump('TYPE_COMPRESSED', $size, $this->buffer->bytes());

                    return $this->read();
                case self::TYPE_UNCOMPRESSED:
                    $this->buffer->discard(self::SIZE_CHECKSUM);

                    return $this->buffer->consume($size - 4);
                case self::TYPE_PADDING:
                    var_dump('TYPE_PADDING', $size, $this->buffer->bytes());

                    return $this->read();
                default:
                    throw Exception\SnappyException::invalidHeader();
            }
        });
    }

    public function write(string $data): Promise
    {
        return call(function () use ($data) {
            $result = \pack('CCCCCCCCCC', ...self::IDENTIFIER);

            foreach (\str_split($data, self::SIZE_CHUNK) as $chunk) {
                $result .= $this->compress($chunk);
            }

            return $this->stream->write($result);
        });
    }

    public function close(): void
    {
        $this->stream->close();
    }

    private function compress(string $chunk): string
    {
        $compressed = snappy_compress($chunk);

        [$type, $data] = \strlen($compressed) <= 0.875 * \strlen($chunk)
            ? [self::TYPE_COMPRESSED, $compressed]
            : [self::TYPE_UNCOMPRESSED, $chunk];

        $checksum = \unpack('N', \hash('crc32c', $chunk, true))[1];
        $checksum = (($checksum >> 15) | ($checksum << 17)) + 0xa282ead8 & 0xffffffff;

        $size = (\strlen($chunk) + 4) << 8;

        return \pack('VV', $type + $size, $checksum) . $data;
    }
}
