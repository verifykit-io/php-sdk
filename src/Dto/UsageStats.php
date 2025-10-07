<?php

declare(strict_types=1);

namespace VerifyKit\Dto;

/**
 * API usage statistics
 */
final readonly class UsageStats
{
    public function __construct(
        public int $current,
        public int $limit,
        public int $remaining,
        public float $percentage,
        public string $periodStart,
        public string $periodEnd
    ) {
    }

    /**
     * Create from array response
     */
    public static function fromArray(array $data): self
    {
        return new self(
            current: $data['current'] ?? 0,
            limit: $data['limit'] ?? 0,
            remaining: $data['remaining'] ?? 0,
            percentage: $data['percentage'] ?? 0.0,
            periodStart: $data['period_start'] ?? '',
            periodEnd: $data['period_end'] ?? ''
        );
    }
}
