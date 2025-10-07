<?php

declare(strict_types=1);

namespace VerifyKit\Dto;

/**
 * Rate limit information
 */
final readonly class RateLimitInfo
{
    public function __construct(
        public int $limit,
        public int $remaining,
        public int $reset,
        public ?int $retryAfter = null
    ) {
    }

    /**
     * Create from array response
     */
    public static function fromArray(array $data): self
    {
        return new self(
            limit: $data['limit'] ?? 0,
            remaining: $data['remaining'] ?? 0,
            reset: $data['reset'] ?? 0,
            retryAfter: $data['retryAfter'] ?? null
        );
    }
}
