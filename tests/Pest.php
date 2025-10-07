<?php

declare(strict_types=1);

use VerifyKit\Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(TestCase::class)->in('Unit', 'Integration');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function getTestApiKey(): string
{
    return 'vk_test_12345678901234567890123456789012';
}

function getMockValidationResponse(): array
{
    return [
        'email' => 'test@example.com',
        'valid' => true,
        'reachable' => 'valid',
        'syntax' => [
            'valid' => true,
            'username' => 'test',
            'domain' => 'example.com',
        ],
        'mx' => [
            'valid' => true,
            'records' => ['mx1.example.com', 'mx2.example.com'],
        ],
        'smtp' => [
            'valid' => true,
            'state' => 'deliverable',
        ],
        'disposable' => false,
        'role_based' => false,
        'free_email' => false,
        'score' => 0.95,
        'quality_grade' => 'excellent',
        'reason' => 'Valid email address',
    ];
}
