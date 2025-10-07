<?php

declare(strict_types=1);

namespace VerifyKit\Dto;

/**
 * Single email validation result
 */
final readonly class ValidationResult
{
    public function __construct(
        public string $email,
        public bool $valid,
        public string $reachable,
        public SyntaxValidation $syntax,
        public MxValidation $mx,
        public ?SmtpValidation $smtp,
        public bool $disposable,
        public bool $roleBased,
        public bool $freeEmail,
        public float $score,
        public ?string $qualityGrade = null,
        public ?string $reason = null,
        public ?string $didYouMean = null
    ) {
    }

    /**
     * Create from array response
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'] ?? '',
            valid: $data['valid'] ?? false,
            reachable: $data['reachable'] ?? 'unknown',
            syntax: SyntaxValidation::fromArray($data['syntax'] ?? []),
            mx: MxValidation::fromArray($data['mx'] ?? []),
            smtp: isset($data['smtp']) ? SmtpValidation::fromArray($data['smtp']) : null,
            disposable: $data['disposable'] ?? false,
            roleBased: $data['role_based'] ?? false,
            freeEmail: $data['free_email'] ?? false,
            score: $data['score'] ?? 0.0,
            qualityGrade: $data['quality_grade'] ?? null,
            reason: $data['reason'] ?? null,
            didYouMean: $data['did_you_mean'] ?? null
        );
    }
}
