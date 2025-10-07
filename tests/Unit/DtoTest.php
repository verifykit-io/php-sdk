<?php

declare(strict_types=1);

use VerifyKit\Dto\ValidationResult;
use VerifyKit\Dto\SyntaxValidation;
use VerifyKit\Dto\MxValidation;
use VerifyKit\Dto\SmtpValidation;
use VerifyKit\Dto\UsageStats;
use VerifyKit\Dto\BulkValidationResult;

test('ValidationResult can be created from array', function () {
    $data = getMockValidationResponse();
    $result = ValidationResult::fromArray($data);

    expect($result->email)->toBe('test@example.com');
    expect($result->valid)->toBeTrue();
    expect($result->reachable)->toBe('valid');
    expect($result->disposable)->toBeFalse();
    expect($result->roleBased)->toBeFalse();
    expect($result->freeEmail)->toBeFalse();
    expect($result->score)->toBe(0.95);
    expect($result->qualityGrade)->toBe('excellent');
});

test('SyntaxValidation can be created from array', function () {
    $data = [
        'valid' => true,
        'username' => 'test',
        'domain' => 'example.com',
    ];

    $result = SyntaxValidation::fromArray($data);

    expect($result->valid)->toBeTrue();
    expect($result->username)->toBe('test');
    expect($result->domain)->toBe('example.com');
});

test('MxValidation can be created from array', function () {
    $data = [
        'valid' => true,
        'records' => ['mx1.example.com', 'mx2.example.com'],
    ];

    $result = MxValidation::fromArray($data);

    expect($result->valid)->toBeTrue();
    expect($result->records)->toHaveCount(2);
    expect($result->records[0])->toBe('mx1.example.com');
});

test('SmtpValidation can be created from array', function () {
    $data = [
        'valid' => true,
        'state' => 'deliverable',
    ];

    $result = SmtpValidation::fromArray($data);

    expect($result->valid)->toBeTrue();
    expect($result->state)->toBe('deliverable');
});

test('UsageStats can be created from array', function () {
    $data = [
        'current' => 100,
        'limit' => 1000,
        'remaining' => 900,
        'percentage' => 10.0,
        'period_start' => '2025-10-01',
        'period_end' => '2025-10-31',
    ];

    $result = UsageStats::fromArray($data);

    expect($result->current)->toBe(100);
    expect($result->limit)->toBe(1000);
    expect($result->remaining)->toBe(900);
    expect($result->percentage)->toBe(10.0);
});

test('BulkValidationResult can be created from array', function () {
    $data = [
        'results' => [
            getMockValidationResponse(),
            getMockValidationResponse(),
        ],
        'summary' => [
            'total' => 2,
            'valid' => 2,
            'invalid' => 0,
            'risky' => 0,
            'processing_time_ms' => 100,
            'duplicates_removed' => 0,
        ],
    ];

    $result = BulkValidationResult::fromArray($data);

    expect($result->results)->toHaveCount(2);
    expect($result->summary->total)->toBe(2);
    expect($result->summary->valid)->toBe(2);
});
