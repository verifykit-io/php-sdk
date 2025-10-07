<?php

declare(strict_types=1);

namespace VerifyKit\Dto;

/**
 * Summary statistics for bulk validation
 */
final readonly class BulkValidationSummary
{
    public function __construct(
        public int $total,
        public int $valid,
        public int $invalid,
        public int $risky,
        public int $processingTimeMs,
        public int $duplicatesRemoved,
        public ?bool $quotaExceeded = null,
        public ?int $emailsSkipped = null,
        public ?int $quotaRemaining = null,
        public ?string $message = null
    ) {
    }

    /**
     * Create from array response
     */
    public static function fromArray(array $data): self
    {
        return new self(
            total: $data['total'] ?? 0,
            valid: $data['valid'] ?? 0,
            invalid: $data['invalid'] ?? 0,
            risky: $data['risky'] ?? 0,
            processingTimeMs: $data['processing_time_ms'] ?? 0,
            duplicatesRemoved: $data['duplicates_removed'] ?? 0,
            quotaExceeded: $data['quota_exceeded'] ?? null,
            emailsSkipped: $data['emails_skipped'] ?? null,
            quotaRemaining: $data['quota_remaining'] ?? null,
            message: $data['message'] ?? null
        );
    }
}
