<?php

declare(strict_types=1);

namespace VerifyKit\Dto;

/**
 * Bulk email validation result
 */
final readonly class BulkValidationResult
{
    /**
     * @param ValidationResult[] $results
     */
    public function __construct(
        public array $results,
        public BulkValidationSummary $summary
    ) {
    }

    /**
     * Create from array response
     */
    public static function fromArray(array $data): self
    {
        $results = array_map(
            fn(array $result) => ValidationResult::fromArray($result),
            $data['results'] ?? []
        );

        return new self(
            results: $results,
            summary: BulkValidationSummary::fromArray($data['summary'] ?? [])
        );
    }
}
