<?php

declare(strict_types=1);

use VerifyKit\VerifyKit;

/**
 * Integration tests for retry logic and resilience
 *
 * These tests verify the SDK's ability to handle failures gracefully
 */

test('client can be configured with custom retry settings', function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? 'vk_test_12345678901234567890';

    $client = new VerifyKit(
        apiKey: $apiKey,
        maxRetries: 5,
        timeout: 60
    );

    expect($client)->toBeInstanceOf(VerifyKit::class);
})->group('integration');

test('client can be configured with debug mode', function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? 'vk_test_12345678901234567890';

    $client = new VerifyKit(
        apiKey: $apiKey,
        debug: true
    );

    expect($client)->toBeInstanceOf(VerifyKit::class);
})->group('integration');

test('client can be configured with custom headers', function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? 'vk_test_12345678901234567890';

    $client = new VerifyKit(
        apiKey: $apiKey,
        headers: [
            'X-Custom-Header' => 'custom-value',
            'X-Client-Version' => '1.0.0'
        ]
    );

    expect($client)->toBeInstanceOf(VerifyKit::class);
})->group('integration');

test('client works with minimal configuration', function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? null;

    if (!$apiKey) {
        test()->markTestSkipped('VERIFYKIT_API_KEY not set');
    }

    // Just API key, all defaults
    $client = new VerifyKit(apiKey: $apiKey);

    $result = $client->validate('test@example.com');
    expect($result)->toBeInstanceOf(\VerifyKit\Dto\ValidationResult::class);
})->group('integration');

test('client works with all configuration options', function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? null;

    if (!$apiKey) {
        test()->markTestSkipped('VERIFYKIT_API_KEY not set');
    }

    $client = new VerifyKit(
        apiKey: $apiKey,
        baseUrl: 'https://api.verifykit.io',
        timeout: 45,
        maxRetries: 2,
        debug: false,
        headers: ['X-Test' => 'integration']
    );

    $result = $client->validate('test@example.com');
    expect($result)->toBeInstanceOf(\VerifyKit\Dto\ValidationResult::class);
})->group('integration');

test('metadata is updated after each request', function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? null;

    if (!$apiKey) {
        test()->markTestSkipped('VERIFYKIT_API_KEY not set');
    }

    $client = new VerifyKit(apiKey: $apiKey);

    // Initially no metadata
    expect($client->getLastMetadata())->toBeNull();

    // After first request
    $client->validate('test@example.com');
    $metadata1 = $client->getLastMetadata();
    expect($metadata1)->not->toBeNull();

    // After second request, metadata should be updated
    $client->validate('another@example.com');
    $metadata2 = $client->getLastMetadata();
    expect($metadata2)->not->toBeNull();

    // Metadata should be different (different request IDs)
    if ($metadata1->requestId && $metadata2->requestId) {
        expect($metadata2->requestId)->not->toBe($metadata1->requestId);
    }
})->group('integration');

test('can handle rapid successive requests', function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? null;

    if (!$apiKey) {
        test()->markTestSkipped('VERIFYKIT_API_KEY not set');
    }

    $client = new VerifyKit(apiKey: $apiKey);

    $results = [];
    for ($i = 0; $i < 5; $i++) {
        $results[] = $client->validate("test{$i}@example.com");
    }

    expect($results)->toHaveCount(5);
    foreach ($results as $result) {
        expect($result)->toBeInstanceOf(\VerifyKit\Dto\ValidationResult::class);
    }
})->group('integration');

test('named arguments work for all methods', function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? null;

    if (!$apiKey) {
        test()->markTestSkipped('VERIFYKIT_API_KEY not set');
    }

    $client = new VerifyKit(apiKey: $apiKey);

    // Test validate with named arguments
    $result1 = $client->validate(
        email: 'test@example.com',
        skipSmtp: true
    );
    expect($result1)->toBeInstanceOf(\VerifyKit\Dto\ValidationResult::class);

    // Test validateBulk with named arguments
    $result2 = $client->validateBulk(
        emails: ['test1@example.com', 'test2@example.com'],
        skipSmtp: false
    );
    expect($result2)->toBeInstanceOf(\VerifyKit\Dto\BulkValidationResult::class);
})->group('integration');
