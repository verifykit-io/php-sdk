# VerifyKit PHP SDK

[![Packagist Version](https://img.shields.io/packagist/v/verifykit/sdk.svg)](https://packagist.org/packages/verifykit/sdk)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B%20%7C%208.0%2B-blue.svg)](https://www.php.net/)

Official PHP SDK for [VerifyKit](https://verifykit.io) - The most reliable email validation and verification API.

## Features

✨ **Simple & Intuitive** - Clean API design that's easy to use
🚀 **Fast & Reliable** - Built-in retry logic and error handling
📦 **Modern PHP** - PHP 8 features with PHP 7.4+ compatibility
🔄 **Automatic Retries** - Smart retry logic with exponential backoff
⚡ **Bulk Validation** - Validate up to 1,000 emails in a single request
🛡️ **Rate Limit Handling** - Automatic rate limit detection and retry
📊 **Usage Tracking** - Monitor your API usage and quota
⚠️ **Custom Exceptions** - Detailed exception classes for better error handling
🔍 **Request Metadata** - Access rate limits, cache status, and more

## Requirements

- PHP 7.4 or higher (PHP 8.0+ recommended)
- ext-json
- ext-filter

## Installation

Install via Composer:

```bash
composer require verifykit/sdk
```

## Quick Start

```php
<?php

require 'vendor/autoload.php';

use VerifyKit\VerifyKit;

// Initialize with your API key from environment variable
$client = new VerifyKit(
    apiKey: $_ENV['VERIFYKIT_API_KEY'] // Get your API key from https://verifykit.io/dashboard/api-keys
);

// Validate a single email
$result = $client->validate('user@example.com');
echo $result->valid ? 'Valid' : 'Invalid';
```

## Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [Authentication](#authentication)
- [Usage Examples](#usage-examples)
  - [Single Email Validation](#single-email-validation)
  - [Typo Detection (Did You Mean)](#typo-detection-did-you-mean)
  - [Bulk Email Validation](#bulk-email-validation)
  - [Check Usage](#check-usage)
- [Configuration](#configuration)
- [API Reference](#api-reference)
- [Error Handling](#error-handling)
- [Advanced Features](#advanced-features)
- [Contributing](#contributing)
- [License](#license)

## Authentication

Get your API key from the [VerifyKit Dashboard](https://verifykit.io/dashboard/api-keys).

```php
<?php

use VerifyKit\VerifyKit;

$client = new VerifyKit(
    apiKey: $_ENV['VERIFYKIT_API_KEY']
);
```

**Environment Variables:**
```bash
# .env file
VERIFYKIT_API_KEY=vk_live_your_api_key_here
```

## Usage Examples

### Single Email Validation

```php
<?php

use VerifyKit\VerifyKit;

$client = new VerifyKit(apiKey: $_ENV['VERIFYKIT_API_KEY']);

$result = $client->validate('user@example.com');

echo "Email: {$result->email}\n";
echo "Valid: " . ($result->valid ? 'Yes' : 'No') . "\n";
echo "Reachable: {$result->reachable}\n";  // 'valid', 'invalid', 'risky', 'unknown'
echo "Score: {$result->score}\n";  // 0-1 quality score
echo "Quality Grade: {$result->qualityGrade}\n";  // 'excellent', 'good', 'fair', 'poor'
echo "Disposable: " . ($result->disposable ? 'Yes' : 'No') . "\n";
echo "Role-based: " . ($result->roleBased ? 'Yes' : 'No') . "\n";
echo "Free Email: " . ($result->freeEmail ? 'Yes' : 'No') . "\n";
echo "Reason: {$result->reason}\n";

if ($result->didYouMean) {
    echo "Did you mean: {$result->didYouMean}?\n";
}
```

### Skip SMTP Validation (Faster)

```php
<?php

// Skip SMTP validation for faster results (less accurate)
$result = $client->validate('user@example.com', skipSmtp: true);
```

### Typo Detection (Did You Mean)

The SDK automatically detects common email typos and suggests corrections:

```php
<?php

use VerifyKit\VerifyKit;

$client = new VerifyKit(apiKey: $_ENV['VERIFYKIT_API_KEY']);

// Validate an email with a typo
$result = $client->validate('user@gmial.com'); // Note: "gmial" instead of "gmail"

if ($result->didYouMean) {
    echo "Did you mean: {$result->didYouMean}?\n";
    // Output: "Did you mean: user@gmail.com?"

    // Ask user to confirm or automatically correct
    $confirmedEmail = $result->didYouMean;
    $correctedResult = $client->validate($confirmedEmail);

    echo "Corrected email is " . ($correctedResult->valid ? 'valid' : 'invalid') . "\n";
}

// Common typos detected:
// - gmial.com → gmail.com
// - gmai.com → gmail.com
// - hotmial.com → hotmail.com
// - yaho.com → yahoo.com
// - outlok.com → outlook.com
// And many more...
```

### Bulk Email Validation

```php
<?php

$emails = [
    'john.doe@gmail.com',
    'jane.smith@company.com',
    'invalid@email',
    'test@disposable.com'
];

$result = $client->validateBulk($emails);

// Summary statistics
echo "Total: {$result->summary->total}\n";
echo "Valid: {$result->summary->valid}\n";
echo "Invalid: {$result->summary->invalid}\n";
echo "Risky: {$result->summary->risky}\n";
echo "Processing Time: {$result->summary->processingTimeMs}ms\n";
echo "Duplicates Removed: {$result->summary->duplicatesRemoved}\n";

// Individual results
foreach ($result->results as $email) {
    $status = $email->valid ? '✓' : '✗';
    echo "{$status} {$email->email}\n";
}
```

### Handle Quota Limits

```php
<?php

$result = $client->validateBulk($emails);

if ($result->summary->quotaExceeded) {
    echo "Processed {$result->summary->total} emails\n";
    echo "Skipped {$result->summary->emailsSkipped} emails due to quota\n";
    echo "Remaining quota: {$result->summary->quotaRemaining}\n";
}
```

### Check Usage

```php
<?php

$usage = $client->getUsage();

echo "Current: {$usage->current}\n";           // Current month usage
echo "Limit: {$usage->limit}\n";               // Monthly limit
echo "Remaining: {$usage->remaining}\n";       // Remaining validations
echo "Percentage: {$usage->percentage}%\n";    // Usage percentage
echo "Period Start: {$usage->periodStart}\n";  // Billing period start
echo "Period End: {$usage->periodEnd}\n";      // Billing period end
```

### Get Request Metadata

```php
<?php

$client->validate('user@example.com');

$metadata = $client->getLastMetadata();

if ($metadata) {
    echo "Request ID: {$metadata->requestId}\n";
    echo "Cache: {$metadata->cache}\n";  // 'HIT' or 'MISS'
    echo "Response Time: {$metadata->responseTime}ms\n";

    if ($metadata->rateLimit) {
        echo "Rate Limit: {$metadata->rateLimit->limit}\n";
        echo "Remaining: {$metadata->rateLimit->remaining}\n";
        echo "Reset: {$metadata->rateLimit->reset}\n";
    }

    if ($metadata->usage) {
        echo "Current Usage: {$metadata->usage['current']}/{$metadata->usage['limit']}\n";
    }
}
```

## Configuration

```php
<?php

use VerifyKit\VerifyKit;

$client = new VerifyKit(
    // Required: Your API key (use environment variable)
    apiKey: $_ENV['VERIFYKIT_API_KEY'],

    // Optional: Base URL (default: 'https://api.verifykit.io')
    baseUrl: 'https://api.verifykit.io',

    // Optional: Request timeout in seconds (default: 30)
    timeout: 30,

    // Optional: Maximum number of retries (default: 3)
    maxRetries: 3,

    // Optional: Enable debug logging (default: false)
    debug: true,

    // Optional: Custom headers
    headers: [
        'X-Custom-Header' => 'value'
    ]
);
```

## API Reference

### `validate(string $email, bool $skipSmtp = false): ValidationResult`

Validate a single email address.

**Parameters:**
- `$email` (string): The email address to validate
- `$skipSmtp` (bool): Skip SMTP validation for faster results

**Returns:** `ValidationResult`

**Example:**
```php
$result = $client->validate('user@example.com', skipSmtp: false);
```

### `validateBulk(array $emails, bool $skipSmtp = false): BulkValidationResult`

Validate multiple email addresses at once (up to 1,000).

**Parameters:**
- `$emails` (string[]): Array of email addresses to validate
- `$skipSmtp` (bool): Skip SMTP validation for faster results

**Returns:** `BulkValidationResult`

**Example:**
```php
$result = $client->validateBulk([
    'user1@example.com',
    'user2@example.com'
]);
```

### `getUsage(): UsageStats`

Get current API usage statistics.

**Returns:** `UsageStats`

**Example:**
```php
$usage = $client->getUsage();
echo "Used: {$usage->current}/{$usage->limit}";
```

### `getLastMetadata(): ?ResponseMetadata`

Get metadata from the last API request.

**Returns:** `ResponseMetadata|null`

**Example:**
```php
$metadata = $client->getLastMetadata();
echo $metadata?->requestId;
```

## Error Handling

The SDK provides detailed exception classes for different scenarios:

```php
<?php

use VerifyKit\VerifyKit;
use VerifyKit\Exception\ValidationException;
use VerifyKit\Exception\AuthenticationException;
use VerifyKit\Exception\RateLimitException;
use VerifyKit\Exception\QuotaExceededException;
use VerifyKit\Exception\TimeoutException;
use VerifyKit\Exception\NetworkException;
use VerifyKit\Exception\ServerException;
use VerifyKit\Exception\VerifyKitException;

$client = new VerifyKit(apiKey: $_ENV['VERIFYKIT_API_KEY']);

try {
    $result = $client->validate('invalid-email');
} catch (ValidationException $e) {
    echo "Invalid email format: {$e->getMessage()}\n";
} catch (AuthenticationException $e) {
    echo "Invalid API key: {$e->getMessage()}\n";
} catch (RateLimitException $e) {
    echo "Rate limit exceeded, retry after: {$e->retryAfter}\n";
} catch (QuotaExceededException $e) {
    echo "Monthly quota exceeded: {$e->getMessage()}\n";
    echo "Upgrade at: {$e->upgradeUrl}\n";
} catch (TimeoutException $e) {
    echo "Request timeout: {$e->timeout}s\n";
} catch (NetworkException $e) {
    echo "Network error: {$e->getMessage()}\n";
} catch (ServerException $e) {
    echo "Server error, request ID: {$e->requestId}\n";
} catch (VerifyKitException $e) {
    echo "VerifyKit error: {$e->getMessage()}\n";
} catch (\Exception $e) {
    echo "Unknown error: {$e->getMessage()}\n";
}
```

### Exception Properties

All VerifyKit exceptions include:
- `getMessage()`: Human-readable error message
- `code`: Machine-readable error code
- `statusCode`: HTTP status code
- `requestId`: Request ID for debugging
- `documentation`: Link to relevant documentation

## Advanced Features

### Automatic Retries

The SDK automatically retries failed requests with exponential backoff:

```php
<?php

$client = new VerifyKit(
    apiKey: 'vk_live_...',
    maxRetries: 5 // Retry up to 5 times (default: 3)
);
```

Retries are attempted for:
- Network errors
- Timeout errors
- Server errors (5xx)
- Rate limit errors (with appropriate delay)

### Rate Limit Handling

The SDK automatically handles rate limits by:
1. Detecting rate limit errors (429)
2. Waiting for the `Retry-After` duration
3. Retrying the request automatically

```php
<?php

// The SDK handles this automatically
try {
    $result = $client->validate('user@example.com');
} catch (RateLimitException $e) {
    // Only throws if max retries exceeded
    echo "Still rate limited after retries\n";
}
```

### Debug Logging

Enable debug logging to see detailed request/response information:

```php
<?php

$client = new VerifyKit(
    apiKey: 'vk_live_...',
    debug: true
);

// Logs will show:
// - Request details
// - Response metadata
// - Retry attempts
// - Error information
```

### Custom Timeouts

Configure request timeouts:

```php
<?php

$client = new VerifyKit(
    apiKey: 'vk_live_...',
    timeout: 60 // 60 seconds
);
```

## Testing

The SDK includes comprehensive test coverage:

### Running Tests

```bash
# Run all tests
composer test

# Run only unit tests (fast, no API key required)
./vendor/bin/pest tests/Unit

# Run integration tests (requires VERIFYKIT_API_KEY)
export VERIFYKIT_API_KEY=vk_test_your_key
./vendor/bin/pest --group=integration

# Generate coverage report
composer test:coverage
```

### Test Categories

- **Unit Tests** (`tests/Unit/`) - Fast tests without API calls
- **Integration Tests** (`tests/Integration/`) - Real API tests
  - API functionality tests
  - Error handling tests
  - Retry logic tests
  - Performance tests

See [tests/README.md](./tests/README.md) for detailed testing documentation.

## Examples

Check out the [examples](./examples) directory for more usage examples:

- `basic-usage.php` - Basic single email validation
- `bulk-validation.php` - Bulk email validation
- `typo-detection.php` - Email typo detection and correction
- `error-handling.php` - Comprehensive error handling

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

- 📧 Email: [support@verifykit.io](mailto:support@verifykit.io)
- 📖 Documentation: [https://verifykit.io/docs](https://verifykit.io/docs)

## License

MIT © Nuno Miguel Duarte Unip. Lda

---

Made with ♥ by the VerifyKit team
