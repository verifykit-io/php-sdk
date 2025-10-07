<?php

declare(strict_types=1);

namespace VerifyKit\Dto;

/**
 * Email syntax validation result
 */
final readonly class SyntaxValidation
{
    public function __construct(
        public bool $valid,
        public string $username,
        public string $domain
    ) {
    }

    /**
     * Create from array response
     */
    public static function fromArray(array $data): self
    {
        return new self(
            valid: $data['valid'] ?? false,
            username: $data['username'] ?? '',
            domain: $data['domain'] ?? ''
        );
    }
}
