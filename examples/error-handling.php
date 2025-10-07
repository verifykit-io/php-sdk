<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use VerifyKit\VerifyKit;
use VerifyKit\Exception\ValidationException;
use VerifyKit\Exception\AuthenticationException;
use VerifyKit\Exception\RateLimitException;
use VerifyKit\Exception\QuotaExceededException;
use VerifyKit\Exception\TimeoutException;
use VerifyKit\Exception\NetworkException;
use VerifyKit\Exception\ServerException;
use VerifyKit\Exception\ConfigurationException;
use VerifyKit\Exception\VerifyKitException;

/**
 * Error Handling Example
 *
 * This example demonstrates comprehensive error handling for all
 * possible error scenarios.
 */

echo "=== VerifyKit PHP SDK Error Handling Examples ===\n\n";

// Example 1: Configuration errors
echo "1. Configuration Errors\n";
echo "------------------------\n";

try {
    $client = new VerifyKit(apiKey: ''); // Empty API key
} catch (ConfigurationException $e) {
    echo "✓ Caught configuration error: {$e->getMessage()}\n";
}

try {
    $client = new VerifyKit(apiKey: 'invalid_key'); // Invalid format
} catch (ConfigurationException $e) {
    echo "✓ Caught configuration error: {$e->getMessage()}\n";
}

try {
    $client = new VerifyKit(
        apiKey: 'vk_test_12345',
        timeout: -1 // Invalid timeout
    );
} catch (ConfigurationException $e) {
    echo "✓ Caught configuration error: {$e->getMessage()}\n";
}

echo "\n";

// Example 2: Validation errors
echo "2. Validation Errors\n";
echo "--------------------\n";

$client = new VerifyKit(
    apiKey: $_ENV['VERIFYKIT_API_KEY'] ?? 'vk_test_your_api_key_here'
);

try {
    $client->validate(''); // Empty email
} catch (ValidationException $e) {
    echo "✓ Caught validation error: {$e->getMessage()}\n";
}

try {
    $client->validate('not-an-email'); // Invalid format
} catch (ValidationException $e) {
    echo "✓ Caught validation error: {$e->getMessage()}\n";
}

try {
    $client->validateBulk([]); // Empty array
} catch (ValidationException $e) {
    echo "✓ Caught validation error: {$e->getMessage()}\n";
}

try {
    $emails = array_fill(0, 1001, 'test@example.com');
    $client->validateBulk($emails); // Too many emails
} catch (ValidationException $e) {
    echo "✓ Caught validation error: {$e->getMessage()}\n";
}

echo "\n";

// Example 3: Comprehensive error handling
echo "3. Comprehensive Error Handling\n";
echo "-------------------------------\n";

function validateEmailWithErrorHandling(VerifyKit $client, string $email): void
{
    try {
        $result = $client->validate($email);
        echo "✓ Email validated successfully: {$email}\n";
        echo "  Valid: " . ($result->valid ? 'Yes' : 'No') . "\n";

    } catch (ValidationException $e) {
        echo "✗ Validation error: {$e->getMessage()}\n";
        echo "  Error code: {$e->code}\n";
        if ($e->details) {
            echo "  Details:\n";
            foreach ($e->details as $detail) {
                echo "    - {$detail['field']}: {$detail['message']}\n";
            }
        }

    } catch (AuthenticationException $e) {
        echo "✗ Authentication error: {$e->getMessage()}\n";
        echo "  Please check your API key\n";
        echo "  Error code: {$e->code}\n";

    } catch (RateLimitException $e) {
        echo "✗ Rate limit exceeded: {$e->getMessage()}\n";
        echo "  Retry after: {$e->retryAfter} seconds\n";
        echo "  Rate limit: {$e->remaining}/{$e->limit}\n";
        echo "  Waiting and retrying...\n";

        // In a real application, you might want to wait and retry
        if ($e->retryAfter) {
            sleep($e->retryAfter);
            // Retry the request...
        }

    } catch (QuotaExceededException $e) {
        echo "✗ Monthly quota exceeded: {$e->getMessage()}\n";
        echo "  Current usage: {$e->currentUsage}/{$e->monthlyLimit}\n";
        if ($e->upgradeUrl) {
            echo "  Upgrade at: {$e->upgradeUrl}\n";
        }

    } catch (TimeoutException $e) {
        echo "✗ Request timeout: {$e->getMessage()}\n";
        echo "  Timeout: {$e->timeout} seconds\n";
        echo "  Consider increasing the timeout or checking your network\n";

    } catch (NetworkException $e) {
        echo "✗ Network error: {$e->getMessage()}\n";
        echo "  Please check your internet connection\n";

    } catch (ServerException $e) {
        echo "✗ Server error: {$e->getMessage()}\n";
        echo "  Status code: {$e->statusCode}\n";
        echo "  Request ID: {$e->requestId}\n";
        echo "  This is likely a temporary issue. Please try again later.\n";

    } catch (VerifyKitException $e) {
        echo "✗ VerifyKit error: {$e->getMessage()}\n";
        echo "  Error code: {$e->code}\n";
        echo "  Status code: {$e->statusCode}\n";
        if ($e->requestId) {
            echo "  Request ID: {$e->requestId}\n";
        }
        if ($e->documentation) {
            echo "  Documentation: {$e->documentation}\n";
        }

    } catch (\Exception $e) {
        echo "✗ Unknown error: {$e->getMessage()}\n";
        echo "  Type: " . get_class($e) . "\n";
    }
}

// Test with valid email
validateEmailWithErrorHandling($client, 'valid@example.com');
echo "\n";

// Test with invalid email
validateEmailWithErrorHandling($client, 'invalid-email');
echo "\n";

echo "=== Error Handling Complete ===\n";
