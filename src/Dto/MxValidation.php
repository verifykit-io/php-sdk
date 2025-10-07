<?php

declare(strict_types=1);

namespace VerifyKit\Dto;

/**
 * MX record validation result
 */
final readonly class MxValidation
{
    /**
     * @param string[] $records
     */
    public function __construct(
        public bool $valid,
        public array $records
    ) {
    }

    /**
     * Create from array response
     */
    public static function fromArray(array $data): self
    {
        return new self(
            valid: $data['valid'] ?? false,
            records: $data['records'] ?? []
        );
    }
}
