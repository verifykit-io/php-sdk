<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use VerifyKit\VerifyKit;

/**
 * Basic Email Validation Example
 *
 * This example demonstrates how to validate a single email address.
 */

// Initialize the client with your API key
$client = new VerifyKit(
    apiKey: $_ENV['VERIFYKIT_API_KEY'] ?? 'vk_test_your_api_key_here'
);

// Validate a single email
try {
    $result = $client->validate('user@example.com');

    echo "=== Email Validation Result ===\n\n";
    echo "Email: {$result->email}\n";
    echo "Valid: " . ($result->valid ? 'Yes ✓' : 'No ✗') . "\n";
    echo "Reachable: {$result->reachable}\n";
    echo "Score: {$result->score}\n";
    echo "Quality Grade: {$result->qualityGrade}\n";
    echo "Disposable: " . ($result->disposable ? 'Yes' : 'No') . "\n";
    echo "Role-based: " . ($result->roleBased ? 'Yes' : 'No') . "\n";
    echo "Free Email: " . ($result->freeEmail ? 'Yes' : 'No') . "\n";

    if ($result->reason) {
        echo "Reason: {$result->reason}\n";
    }

    if ($result->didYouMean) {
        echo "Did you mean: {$result->didYouMean}?\n";
    }

    echo "\n=== Syntax Details ===\n";
    echo "Username: {$result->syntax->username}\n";
    echo "Domain: {$result->syntax->domain}\n";

    echo "\n=== MX Records ===\n";
    echo "Valid: " . ($result->mx->valid ? 'Yes' : 'No') . "\n";
    echo "Records: " . implode(', ', $result->mx->records) . "\n";

    if ($result->smtp) {
        echo "\n=== SMTP Validation ===\n";
        echo "Valid: " . ($result->smtp->valid ? 'Yes' : 'No') . "\n";
        echo "State: {$result->smtp->state}\n";
    }

    // Get request metadata
    $metadata = $client->getLastMetadata();
    if ($metadata) {
        echo "\n=== Request Metadata ===\n";
        echo "Request ID: {$metadata->requestId}\n";
        echo "Cache: {$metadata->cache}\n";

        if ($metadata->rateLimit) {
            echo "Rate Limit: {$metadata->rateLimit->remaining}/{$metadata->rateLimit->limit}\n";
        }
    }

} catch (\Exception $e) {
    echo "Error: {$e->getMessage()}\n";
    if ($e instanceof \VerifyKit\Exception\VerifyKitException) {
        echo "Error Code: {$e->code}\n";
        echo "Status Code: {$e->statusCode}\n";
    }
}
