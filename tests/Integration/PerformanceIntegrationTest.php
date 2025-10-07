<?php

declare(strict_types=1);

use VerifyKit\VerifyKit;

/**
 * Performance and load integration tests
 *
 * These tests verify the SDK performs well under various conditions
 */

test('single validation completes within reasonable time', function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? null;

    if (!$apiKey) {
        test()->markTestSkipped('VERIFYKIT_API_KEY not set');
    }

    $client = new VerifyKit(apiKey: $apiKey);

    $start = microtime(true);
    $result = $client->validate('test@example.com');
    $duration = (microtime(true) - $start) * 1000; // Convert to milliseconds

    expect($result)->toBeInstanceOf(\VerifyKit\Dto\ValidationResult::class);

    // Should complete within 10 seconds (generous timeout)
    expect($duration)->toBeLessThan(10000);
})->group('integration')->group('performance');

test('bulk validation is faster than individual validations', function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? null;

    if (!$apiKey) {
        test()->markTestSkipped('VERIFYKIT_API_KEY not set');
    }

    $client = new VerifyKit(apiKey: $apiKey);

    $emails = [
        'test1@example.com',
        'test2@example.com',
        'test3@example.com',
        'test4@example.com',
        'test5@example.com',
    ];

    // Time individual validations
    $startIndividual = microtime(true);
    foreach ($emails as $email) {
        $client->validate($email);
    }
    $individualDuration = (microtime(true) - $startIndividual) * 1000;

    // Time bulk validation
    $startBulk = microtime(true);
    $client->validateBulk($emails);
    $bulkDuration = (microtime(true) - $startBulk) * 1000;

    // Bulk should be significantly faster (at least 2x)
    expect($bulkDuration)->toBeLessThan($individualDuration / 2);
})->group('integration')->group('performance')->group('slow');

test('skip smtp option improves validation speed', function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? null;

    if (!$apiKey) {
        test()->markTestSkipped('VERIFYKIT_API_KEY not set');
    }

    $client = new VerifyKit(apiKey: $apiKey);

    // Validate with SMTP
    $start1 = microtime(true);
    $result1 = $client->validate('test@example.com', skipSmtp: false);
    $withSmtpDuration = (microtime(true) - $start1) * 1000;

    // Validate without SMTP
    $start2 = microtime(true);
    $result2 = $client->validate('test@example.com', skipSmtp: true);
    $withoutSmtpDuration = (microtime(true) - $start2) * 1000;

    expect($result1)->toBeInstanceOf(\VerifyKit\Dto\ValidationResult::class);
    expect($result2)->toBeInstanceOf(\VerifyKit\Dto\ValidationResult::class);

    // Without SMTP should be faster or equal
    expect($withoutSmtpDuration)->toBeLessThanOrEqual($withSmtpDuration);
})->group('integration')->group('performance');

test('metadata tracking has minimal overhead', function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? null;

    if (!$apiKey) {
        test()->markTestSkipped('VERIFYKIT_API_KEY not set');
    }

    $client = new VerifyKit(apiKey: $apiKey);

    $start = microtime(true);

    for ($i = 0; $i < 10; $i++) {
        $client->validate("test{$i}@example.com");
        $metadata = $client->getLastMetadata();
    }

    $duration = (microtime(true) - $start) * 1000;

    // Should complete 10 validations + metadata fetches within reasonable time
    expect($duration)->toBeLessThan(30000); // 30 seconds for 10 requests
})->group('integration')->group('performance');

test('handles 50 emails in bulk efficiently', function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? null;

    if (!$apiKey) {
        test()->markTestSkipped('VERIFYKIT_API_KEY not set');
    }

    $client = new VerifyKit(apiKey: $apiKey);

    $emails = array_map(
        fn($i) => "test{$i}@example.com",
        range(1, 50)
    );

    $start = microtime(true);
    $result = $client->validateBulk($emails);
    $duration = (microtime(true) - $start) * 1000;

    expect($result->summary->total)->toBe(50);
    expect($result->results)->toHaveCount(50);

    // Should complete within 30 seconds
    expect($duration)->toBeLessThan(30000);

    // Processing time reported by API should be reasonable
    expect($result->summary->processingTimeMs)->toBeGreaterThan(0);
    expect($result->summary->processingTimeMs)->toBeLessThan(60000);
})->group('integration')->group('performance')->group('slow');

test('duplicate removal is efficient', function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? null;

    if (!$apiKey) {
        test()->markTestSkipped('VERIFYKIT_API_KEY not set');
    }

    $client = new VerifyKit(apiKey: $apiKey);

    // Create array with many duplicates
    $emails = array_merge(
        array_fill(0, 50, 'duplicate@example.com'),
        ['unique1@example.com', 'unique2@example.com']
    );

    $start = microtime(true);
    $result = $client->validateBulk($emails);
    $duration = (microtime(true) - $start) * 1000;

    // Should only process 3 unique emails
    expect($result->summary->total)->toBe(3);

    // Should be fast since only 3 emails are actually validated
    expect($duration)->toBeLessThan(5000);
})->group('integration')->group('performance');

test('client can handle multiple instances concurrently', function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? null;

    if (!$apiKey) {
        test()->markTestSkipped('VERIFYKIT_API_KEY not set');
    }

    // Create multiple client instances
    $clients = [
        new VerifyKit(apiKey: $apiKey),
        new VerifyKit(apiKey: $apiKey),
        new VerifyKit(apiKey: $apiKey),
    ];

    $results = [];
    foreach ($clients as $i => $client) {
        $results[] = $client->validate("test{$i}@example.com");
    }

    expect($results)->toHaveCount(3);
    foreach ($results as $result) {
        expect($result)->toBeInstanceOf(\VerifyKit\Dto\ValidationResult::class);
    }
})->group('integration')->group('performance');
