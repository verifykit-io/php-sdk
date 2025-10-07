<?php

declare(strict_types=1);

namespace VerifyKit\Dto;

/**
 * Response metadata included in headers
 */
final readonly class ResponseMetadata
{
    public function __construct(
        public ?string $requestId = null,
        public ?string $cache = null,
        public ?int $responseTime = null,
        public ?RateLimitInfo $rateLimit = null,
        public ?array $usage = null
    ) {
    }

    /**
     * Create from response headers
     */
    public static function fromHeaders(array $headers): self
    {
        $rateLimit = null;
        if (isset($headers['x-ratelimit-limit'])) {
            $rateLimit = new RateLimitInfo(
                limit: (int) $headers['x-ratelimit-limit'],
                remaining: (int) ($headers['x-ratelimit-remaining'] ?? 0),
                reset: (int) ($headers['x-ratelimit-reset'] ?? 0),
                retryAfter: isset($headers['retry-after']) ? (int) $headers['retry-after'] : null
            );
        }

        $usage = null;
        if (isset($headers['x-usage-current']) && isset($headers['x-usage-limit'])) {
            $usage = [
                'current' => (int) $headers['x-usage-current'],
                'limit' => (int) $headers['x-usage-limit'],
            ];
        }

        return new self(
            requestId: $headers['x-request-id'] ?? null,
            cache: $headers['x-cache'] ?? null,
            responseTime: isset($headers['x-response-time']) ? (int) $headers['x-response-time'] : null,
            rateLimit: $rateLimit,
            usage: $usage
        );
    }
}
