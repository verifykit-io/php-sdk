<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use VerifyKit\VerifyKit;

/**
 * Bulk Email Validation Example
 *
 * This example demonstrates how to validate multiple email addresses at once.
 */

// Initialize the client
$client = new VerifyKit(
    apiKey: $_ENV['VERIFYKIT_API_KEY'] ?? 'vk_test_your_api_key_here'
);

// List of emails to validate
$emails = [
    'john.doe@gmail.com',
    'jane.smith@company.com',
    'invalid@email',
    'test@disposable.com',
    'admin@example.com',
    'info@company.org',
    'user@gmial.com', // Typo
    'support@business.net'
];

try {
    echo "Validating " . count($emails) . " emails...\n\n";

    $result = $client->validateBulk($emails);

    // Display summary
    echo "=== Validation Summary ===\n\n";
    echo "Total: {$result->summary->total}\n";
    echo "Valid: {$result->summary->valid} ✓\n";
    echo "Invalid: {$result->summary->invalid} ✗\n";
    echo "Risky: {$result->summary->risky} ⚠\n";
    echo "Processing Time: {$result->summary->processingTimeMs}ms\n";
    echo "Duplicates Removed: {$result->summary->duplicatesRemoved}\n";

    if ($result->summary->quotaExceeded) {
        echo "\n⚠️  Quota Exceeded\n";
        echo "Emails Skipped: {$result->summary->emailsSkipped}\n";
        echo "Remaining Quota: {$result->summary->quotaRemaining}\n";
    }

    // Display individual results
    echo "\n=== Individual Results ===\n\n";
    foreach ($result->results as $emailResult) {
        $status = $emailResult->valid ? '✓' : '✗';
        $symbol = $emailResult->valid ? '✓' :
            ($emailResult->reachable === 'risky' ? '⚠' : '✗');

        echo "{$symbol} {$emailResult->email}\n";
        echo "   Reachable: {$emailResult->reachable}\n";
        echo "   Score: {$emailResult->score}\n";

        if ($emailResult->disposable) {
            echo "   ⚠️  Disposable email\n";
        }

        if ($emailResult->roleBased) {
            echo "   ℹ️  Role-based email\n";
        }

        if ($emailResult->didYouMean) {
            echo "   💡 Did you mean: {$emailResult->didYouMean}?\n";
        }

        echo "\n";
    }

    // Get usage stats
    echo "=== Usage Stats ===\n\n";
    $usage = $client->getUsage();
    echo "Used: {$usage->current}/{$usage->limit}\n";
    echo "Remaining: {$usage->remaining}\n";
    echo "Usage: {$usage->percentage}%\n";

} catch (\Exception $e) {
    echo "Error: {$e->getMessage()}\n";
    if ($e instanceof \VerifyKit\Exception\VerifyKitException) {
        echo "Error Code: {$e->code}\n";
        echo "Status Code: {$e->statusCode}\n";
    }
}
