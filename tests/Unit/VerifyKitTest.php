<?php

declare(strict_types=1);

use VerifyKit\Exception\ConfigurationException;
use VerifyKit\Exception\ValidationException;
use VerifyKit\VerifyKit;

test('constructor validates api key is required', function () {
    new VerifyKit(apiKey: '');
})->throws(ConfigurationException::class, 'API key is required');

test('constructor validates api key format', function () {
    new VerifyKit(apiKey: 'invalid_key');
})->throws(ConfigurationException::class, 'Invalid API key format');

test('constructor accepts valid live api key', function () {
    $client = new VerifyKit(apiKey: 'vk_live_12345678901234567890123456789012');
    expect($client)->toBeInstanceOf(VerifyKit::class);
});

test('constructor accepts valid test api key', function () {
    $client = new VerifyKit(apiKey: 'vk_test_12345678901234567890123456789012');
    expect($client)->toBeInstanceOf(VerifyKit::class);
});

test('constructor validates timeout is positive', function () {
    new VerifyKit(apiKey: getTestApiKey(), timeout: 0);
})->throws(ConfigurationException::class, 'Timeout must be greater than 0');

test('constructor validates max retries is non-negative', function () {
    new VerifyKit(apiKey: getTestApiKey(), maxRetries: -1);
})->throws(ConfigurationException::class, 'Max retries must be 0 or greater');

test('validate throws error for empty email', function () {
    $client = new VerifyKit(apiKey: getTestApiKey());
    $client->validate('');
})->throws(ValidationException::class, 'Email is required');

test('validate throws error for invalid email format', function () {
    $client = new VerifyKit(apiKey: getTestApiKey());
    $client->validate('not-an-email');
})->throws(ValidationException::class, 'Invalid email format');

test('validate throws error for invalid webhook url', function () {
    $client = new VerifyKit(apiKey: getTestApiKey());
    $client->validate('test@example.com', webhook: 'not-a-url');
})->throws(ValidationException::class, 'Invalid webhook URL');

test('validateBulk throws error for empty array', function () {
    $client = new VerifyKit(apiKey: getTestApiKey());
    $client->validateBulk([]);
})->throws(ValidationException::class, 'Emails array cannot be empty');

test('validateBulk throws error for too many emails', function () {
    $client = new VerifyKit(apiKey: getTestApiKey());
    $emails = array_fill(0, 1001, 'test@example.com');
    $client->validateBulk($emails);
})->throws(ValidationException::class, 'Maximum 1000 emails per request');

test('validateBulk throws error for invalid email in array', function () {
    $client = new VerifyKit(apiKey: getTestApiKey());
    $client->validateBulk(['valid@example.com', 'invalid-email']);
})->throws(ValidationException::class, 'Invalid email format');

test('getLastMetadata returns null initially', function () {
    $client = new VerifyKit(apiKey: getTestApiKey());
    expect($client->getLastMetadata())->toBeNull();
});
