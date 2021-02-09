<?php

declare(strict_types=1);

namespace PHPinnacle\NSQ\Config;

use PHPinnacle\NSQ\Exception;

final class ClientConfig
{
    public function __construct(
        public ?string $authSecret = null,
        public int $connectTimeout = 10,
        public string $clientId = '',
        public bool $deflate = false,
        public int $deflateLevel = 6,
        public int $heartbeatInterval = 30000,
        public string $hostname = '',
        public int $msgTimeout = 60000,
        public int $sampleRate = 0,
        public bool $featureNegotiation = true,
        public bool $tls = false,
        public bool $snappy = false,
        public int $readTimeout = 5,
        public string $userAgent = '',
    ) {
        $this->featureNegotiation = true;

        if ('' === $this->hostname) {
            $this->hostname = \gethostname() ?: '';
        }

        if ($this->snappy && $this->deflate) {
            throw new Exception\ClientException('Client cannot enable both [snappy] and [deflate]');
        }
    }

    public function toString(): string
    {
        return json_encode([
            'client_id' => $this->clientId,
            'deflate' => $this->deflate,
            'deflate_level' => $this->deflateLevel,
            'feature_negotiation' => $this->featureNegotiation,
            'heartbeat_interval' => $this->heartbeatInterval,
            'hostname' => $this->hostname,
            'msg_timeout' => $this->msgTimeout,
            'sample_rate' => $this->sampleRate,
            'snappy' => $this->snappy,
            'tls_v1' => $this->tls,
            'user_agent' => $this->userAgent,
        ], JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT);
    }
}
