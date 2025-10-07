<?php

declare(strict_types=1);

use VerifyKit\Exception\AuthenticationException;
use VerifyKit\Exception\ConfigurationException;
use VerifyKit\Exception\NetworkException;
use VerifyKit\Exception\NotFoundException;
use VerifyKit\Exception\QuotaExceededException;
use VerifyKit\Exception\RateLimitException;
use VerifyKit\Exception\ServerException;
use VerifyKit\Exception\TimeoutException;
use VerifyKit\Exception\ValidationException;
use VerifyKit\Exception\VerifyKitException;

test('VerifyKitException can be created', function () {
    $exception = new VerifyKitException('Test error', 'TEST_ERROR', 400);

    expect($exception)->toBeInstanceOf(VerifyKitException::class);
    expect($exception->getMessage())->toBe('Test error');
    expect($exception->errorCode)->toBe('TEST_ERROR');
    expect($exception->statusCode)->toBe(400);
});

test('VerifyKitException can be created from API error', function () {
    $response = [
        'error' => 'TEST_ERROR',
        'message' => 'Test error message',
        'requestId' => 'req_123',
        'documentation' => 'https://docs.verifykit.io',
    ];

    $exception = VerifyKitException::fromApiError($response, 400);

    expect($exception)->toBeInstanceOf(ValidationException::class);
    expect($exception->getMessage())->toBe('Test error message');
    expect($exception->requestId)->toBe('req_123');
    expect($exception->documentation)->toBe('https://docs.verifykit.io');
});

test('AuthenticationException has correct status code', function () {
    $exception = new AuthenticationException();

    expect($exception->statusCode)->toBe(401);
});

test('RateLimitException stores retry information', function () {
    $exception = new RateLimitException(
        message: 'Rate limit exceeded',
        retryAfter: 60,
        limit: 100,
        remaining: 0
    );

    expect($exception->statusCode)->toBe(429);
    expect($exception->retryAfter)->toBe(60);
    expect($exception->limit)->toBe(100);
    expect($exception->remaining)->toBe(0);
});

test('QuotaExceededException stores quota information', function () {
    $exception = new QuotaExceededException(
        currentUsage: 1000,
        monthlyLimit: 1000,
        upgradeUrl: 'https://verifykit.io/upgrade'
    );

    expect($exception->statusCode)->toBe(429);
    expect($exception->currentUsage)->toBe(1000);
    expect($exception->monthlyLimit)->toBe(1000);
    expect($exception->upgradeUrl)->toBe('https://verifykit.io/upgrade');
});

test('ServerException has correct status code', function () {
    $exception = new ServerException();

    expect($exception->statusCode)->toBe(500);
});

test('NetworkException has correct code', function () {
    $exception = new NetworkException();

    expect($exception->errorCode)->toBe('NETWORK_ERROR');
});

test('TimeoutException stores timeout value', function () {
    $exception = new TimeoutException(timeout: 30);

    expect($exception->errorCode)->toBe('TIMEOUT');
    expect($exception->timeout)->toBe(30);
});

test('ConfigurationException has correct code', function () {
    $exception = new ConfigurationException('Invalid config');

    expect($exception->errorCode)->toBe('INVALID_CONFIG');
});

test('ValidationException can store details', function () {
    $details = [
        ['field' => 'email', 'message' => 'Invalid format'],
    ];

    $exception = new ValidationException(
        message: 'Validation failed',
        details: $details
    );

    expect($exception->details)->toHaveCount(1);
    expect($exception->details[0]['field'])->toBe('email');
});
