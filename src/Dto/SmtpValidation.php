<?php

declare(strict_types=1);

namespace VerifyKit\Dto;

/**
 * SMTP validation result
 */
final readonly class SmtpValidation
{
    public function __construct(
        public bool $valid,
        public string $state
    ) {
    }

    /**
     * Create from array response
     */
    public static function fromArray(array $data): self
    {
        return new self(
            valid: $data['valid'] ?? false,
            state: $data['state'] ?? 'unknown'
        );
    }
}
