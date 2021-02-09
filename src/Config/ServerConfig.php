<?php

declare(strict_types=1);

namespace PHPinnacle\NSQ\Config;

use PHPinnacle\NSQ\Frame;

final class ServerConfig
{
    public function __construct(
        public bool $authRequired,
        public bool $deflate,
        public int $deflateLevel,
        public int $maxDeflateLevel,
        public int $maxMsgTimeout,
        public int $maxRdyCount,
        public int $msgTimeout,
        public int $outputBufferSize,
        public int $outputBufferTimeout,
        public int $sampleRate,
        public bool $snappy,
        public bool $tls,
        public string $version,
    ) {
    }

    public static function fromResponse(Frame $response): self
    {
        return self::fromArray(json_decode($response->data, true));
    }

    public static function fromArray(array $array): self
    {
        return new self(
            authRequired: $array['auth_required'],
            deflate: $array['deflate'],
            deflateLevel: $array['deflate_level'],
            maxDeflateLevel: $array['max_deflate_level'],
            maxMsgTimeout: $array['max_msg_timeout'],
            maxRdyCount: $array['max_rdy_count'],
            msgTimeout: $array['msg_timeout'],
            outputBufferSize: $array['output_buffer_size'],
            outputBufferTimeout: $array['output_buffer_timeout'],
            sampleRate: $array['sample_rate'],
            snappy: $array['snappy'],
            tls: $array['tls_v1'],
            version: $array['version'],
        );
    }
}
