<?php

declare(strict_types=1);

use VerifyKit\VerifyKit;
use VerifyKit\Exception\ValidationException;
use VerifyKit\Exception\ConfigurationException;

/**
 * Integration tests for error handling scenarios
 */

test('throws validation error for empty email array', function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? 'vk_test_12345678901234567890';
    $client = new VerifyKit(apiKey: $apiKey);

    $client->validateBulk([]);
})->group('integration')->throws(ValidationException::class, 'Emails array cannot be empty');

test('throws validation error for too many emails', function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? 'vk_test_12345678901234567890';
    $client = new VerifyKit(apiKey: $apiKey);

    $emails = array_fill(0, 1001, 'test@example.com');
    $client->validateBulk($emails);
})->group('integration')->throws(ValidationException::class, 'Maximum 1000 emails per request');

test('throws validation error for invalid email format in bulk', function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? 'vk_test_12345678901234567890';
    $client = new VerifyKit(apiKey: $apiKey);

    $client->validateBulk(['valid@example.com', 'invalid-email', 'another@example.com']);
})->group('integration')->throws(ValidationException::class, 'Invalid email format');

test('throws configuration error for invalid API key format', function () {
    new VerifyKit(apiKey: 'invalid_key_format');
})->group('integration')->throws(ConfigurationException::class, 'Invalid API key format');

test('throws configuration error for empty API key', function () {
    new VerifyKit(apiKey: '');
})->group('integration')->throws(ConfigurationException::class, 'API key is required');

test('throws configuration error for negative timeout', function () {
    new VerifyKit(
        apiKey: 'vk_test_12345678901234567890',
        timeout: 0
    );
})->group('integration')->throws(ConfigurationException::class, 'Timeout must be greater than 0');

test('throws configuration error for negative max retries', function () {
    new VerifyKit(
        apiKey: 'vk_test_12345678901234567890',
        maxRetries: -1
    );
})->group('integration')->throws(ConfigurationException::class, 'Max retries must be 0 or greater');

test('throws validation error for invalid webhook URL', function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? 'vk_test_12345678901234567890';
    $client = new VerifyKit(apiKey: $apiKey);

    $client->validate('test@example.com', webhook: 'not-a-valid-url');
})->group('integration')->throws(ValidationException::class, 'Invalid webhook URL');

test('handles validation errors with proper error details', function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? 'vk_test_12345678901234567890';
    $client = new VerifyKit(apiKey: $apiKey);

    try {
        $client->validate('');
        expect(false)->toBeTrue('Exception should have been thrown');
    } catch (ValidationException $e) {
        expect($e->getMessage())->toBeString();
        expect($e->errorCode)->toBe('INVALID_EMAIL');
        expect($e->statusCode)->toBeNull(); // Client-side validation
    }
})->group('integration');

test('properly formats all exception properties', function () {
    try {
        new VerifyKit(apiKey: 'invalid');
        expect(false)->toBeTrue('Exception should have been thrown');
    } catch (ConfigurationException $e) {
        expect($e->getMessage())->toContain('Invalid API key format');
        expect($e->errorCode)->toBe('INVALID_CONFIG');
        expect($e->statusCode)->toBeNull();
        expect($e->requestId)->toBeNull();
        expect($e->documentation)->toBeNull();
    }
})->group('integration');
