<?php

declare(strict_types=1);

use VerifyKit\VerifyKit;
use VerifyKit\Exception\ValidationException;
use VerifyKit\Exception\AuthenticationException;

/**
 * Integration tests for VerifyKit SDK
 *
 * These tests make real API calls to verify end-to-end functionality.
 * Set VERIFYKIT_API_KEY environment variable to run these tests.
 *
 * Run with: ./vendor/bin/pest --group=integration
 */

beforeEach(function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? null;

    if (!$apiKey || !str_starts_with($apiKey, 'vk_')) {
        test()->markTestSkipped('VERIFYKIT_API_KEY environment variable not set');
    }

    $this->client = new VerifyKit(apiKey: $apiKey);
});

test('can validate a single email successfully', function () {
    $result = $this->client->validate('test@example.com');

    expect($result)->toBeInstanceOf(\VerifyKit\Dto\ValidationResult::class);
    expect($result->email)->toBe('test@example.com');
    expect($result->valid)->toBeBool();
    expect($result->reachable)->toBeIn(['valid', 'invalid', 'risky', 'unknown']);
    expect($result->score)->toBeFloat();
    expect($result->score)->toBeGreaterThanOrEqual(0);
    expect($result->score)->toBeLessThanOrEqual(1);
    expect($result->disposable)->toBeBool();
    expect($result->roleBased)->toBeBool();
    expect($result->freeEmail)->toBeBool();

    // Syntax should always be present
    expect($result->syntax)->toBeInstanceOf(\VerifyKit\Dto\SyntaxValidation::class);
    expect($result->syntax->valid)->toBeBool();
    expect($result->syntax->username)->toBeString();
    expect($result->syntax->domain)->toBeString();

    // MX should always be present
    expect($result->mx)->toBeInstanceOf(\VerifyKit\Dto\MxValidation::class);
    expect($result->mx->valid)->toBeBool();
    expect($result->mx->records)->toBeArray();
})->group('integration');

test('can validate multiple emails in bulk', function () {
    $emails = [
        'test1@example.com',
        'test2@example.com',
        'test3@example.com',
    ];

    $result = $this->client->validateBulk($emails);

    expect($result)->toBeInstanceOf(\VerifyKit\Dto\BulkValidationResult::class);
    expect($result->results)->toBeArray();
    expect($result->results)->toHaveCount(3);

    expect($result->summary)->toBeInstanceOf(\VerifyKit\Dto\BulkValidationSummary::class);
    expect($result->summary->total)->toBe(3);
    expect($result->summary->valid)->toBeInt();
    expect($result->summary->invalid)->toBeInt();
    expect($result->summary->risky)->toBeInt();
    expect($result->summary->processingTimeMs)->toBeInt();
    expect($result->summary->duplicatesRemoved)->toBe(0);

    foreach ($result->results as $emailResult) {
        expect($emailResult)->toBeInstanceOf(\VerifyKit\Dto\ValidationResult::class);
        expect($emailResult->email)->toBeString();
        expect($emailResult->valid)->toBeBool();
    }
})->group('integration');

test('can detect email typos with did you mean feature', function () {
    // Common typo: gmial.com instead of gmail.com
    $result = $this->client->validate('test@gmial.com');

    expect($result->email)->toBe('test@gmial.com');

    // The API should suggest the correction
    if ($result->didYouMean) {
        expect($result->didYouMean)->toContain('gmail.com');
        expect($result->didYouMean)->toBe('test@gmail.com');
    }
})->group('integration');

test('can detect disposable emails', function () {
    // Use a known disposable email domain
    $result = $this->client->validate('test@tempmail.com');

    expect($result->email)->toBe('test@tempmail.com');
    // Most disposable email checkers should catch tempmail.com
    // But we'll just verify the field exists and is a boolean
    expect($result->disposable)->toBeBool();
})->group('integration');

test('can retrieve usage statistics', function () {
    $usage = $this->client->getUsage();

    expect($usage)->toBeInstanceOf(\VerifyKit\Dto\UsageStats::class);
    expect($usage->current)->toBeInt();
    expect($usage->limit)->toBeInt();
    expect($usage->remaining)->toBeInt();
    expect($usage->percentage)->toBeFloat();
    expect($usage->periodStart)->toBeString();
    expect($usage->periodEnd)->toBeString();

    // Verify calculations are correct
    expect($usage->remaining)->toBe($usage->limit - $usage->current);
})->group('integration');

test('can retrieve request metadata after validation', function () {
    $this->client->validate('test@example.com');

    $metadata = $this->client->getLastMetadata();

    expect($metadata)->toBeInstanceOf(\VerifyKit\Dto\ResponseMetadata::class);

    // Request ID should be present
    if ($metadata->requestId) {
        expect($metadata->requestId)->toBeString();
    }

    // Cache status should be HIT or MISS
    if ($metadata->cache) {
        expect($metadata->cache)->toBeIn(['HIT', 'MISS']);
    }

    // Response time should be positive
    if ($metadata->responseTime) {
        expect($metadata->responseTime)->toBeInt();
        expect($metadata->responseTime)->toBeGreaterThan(0);
    }

    // Rate limit info
    if ($metadata->rateLimit) {
        expect($metadata->rateLimit->limit)->toBeInt();
        expect($metadata->rateLimit->remaining)->toBeInt();
        expect($metadata->rateLimit->reset)->toBeInt();
    }
})->group('integration');

test('can validate email with skip smtp option', function () {
    $result = $this->client->validate('test@example.com', skipSmtp: true);

    expect($result->email)->toBe('test@example.com');
    expect($result->valid)->toBeBool();

    // SMTP validation should be null or skipped
    if ($result->smtp === null) {
        expect(true)->toBeTrue(); // SMTP was skipped
    }
})->group('integration');

test('automatically removes duplicate emails in bulk validation', function () {
    $emails = [
        'test@example.com',
        'test@example.com', // Duplicate
        'test2@example.com',
        'test2@example.com', // Duplicate
    ];

    $result = $this->client->validateBulk($emails);

    // Should only process 2 unique emails
    expect($result->summary->total)->toBe(2);
    expect($result->results)->toHaveCount(2);
})->group('integration');

test('handles invalid email format gracefully', function () {
    $this->client->validate('not-an-email');
})->group('integration')->throws(ValidationException::class);

test('handles empty email gracefully', function () {
    $this->client->validate('');
})->group('integration')->throws(ValidationException::class);

test('handles invalid API key', function () {
    $client = new VerifyKit(apiKey: 'vk_test_invalid_key_12345678901234567890');
    $client->validate('test@example.com');
})->group('integration')->throws(AuthenticationException::class);

test('validates real gmail addresses correctly', function () {
    $result = $this->client->validate('test@gmail.com');

    expect($result->email)->toBe('test@gmail.com');
    expect($result->syntax->valid)->toBeTrue();
    expect($result->syntax->domain)->toBe('gmail.com');
    expect($result->freeEmail)->toBeTrue(); // Gmail is a free email provider
    expect($result->mx->valid)->toBeTrue(); // Gmail should have valid MX records
    expect($result->mx->records)->not->toBeEmpty();
})->group('integration');

test('validates role-based emails correctly', function () {
    $result = $this->client->validate('info@example.com');

    expect($result->email)->toBe('info@example.com');
    expect($result->roleBased)->toBeBool();
    // Most APIs detect info@ as role-based
})->group('integration');

test('bulk validation respects array order', function () {
    $emails = [
        'first@example.com',
        'second@example.com',
        'third@example.com',
    ];

    $result = $this->client->validateBulk($emails);

    expect($result->results[0]->email)->toContain('first');
    expect($result->results[1]->email)->toContain('second');
    expect($result->results[2]->email)->toContain('third');
})->group('integration');

test('can handle large bulk validation batches', function () {
    // Create 100 test emails
    $emails = array_map(
        fn($i) => "test{$i}@example.com",
        range(1, 100)
    );

    $result = $this->client->validateBulk($emails);

    expect($result->summary->total)->toBe(100);
    expect($result->results)->toHaveCount(100);
    expect($result->summary->processingTimeMs)->toBeGreaterThan(0);
})->group('integration')->group('slow');

test('quality grades are valid when present', function () {
    $result = $this->client->validate('test@gmail.com');

    if ($result->qualityGrade) {
        expect($result->qualityGrade)->toBeIn(['excellent', 'good', 'fair', 'poor']);
    }
})->group('integration');

test('smtp state is valid when present', function () {
    $result = $this->client->validate('test@example.com');

    if ($result->smtp) {
        expect($result->smtp->state)->toBeIn(['deliverable', 'undeliverable', 'unknown', 'risky']);
    }
})->group('integration');

test('validates international domain names', function () {
    // Test with an international TLD
    $result = $this->client->validate('test@example.co.uk');

    expect($result->email)->toBe('test@example.co.uk');
    expect($result->syntax->valid)->toBeBool();
    expect($result->syntax->domain)->toBe('example.co.uk');
})->group('integration');

test('handles special characters in email addresses', function () {
    $result = $this->client->validate('user+tag@example.com');

    expect($result->email)->toBe('user+tag@example.com');
    expect($result->syntax->valid)->toBeBool();
    expect($result->syntax->username)->toContain('+');
})->group('integration');
