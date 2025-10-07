# Quick Start Guide - VerifyKit PHP SDK

Get up and running with VerifyKit email validation in less than 5 minutes!

## 1. Installation

```bash
composer require verifykit/sdk
```

## 2. Get Your API Key

1. Sign up at [https://verifykit.io](https://verifykit.io)
2. Get your API key from the [Dashboard](https://verifykit.io/dashboard/api-keys)
3. Set it as an environment variable:

```bash
export VERIFYKIT_API_KEY=vk_live_your_api_key_here
```

## 3. Basic Usage

### Validate a Single Email

```php
<?php

require 'vendor/autoload.php';

use VerifyKit\VerifyKit;

$client = new VerifyKit(apiKey: $_ENV['VERIFYKIT_API_KEY']);

$result = $client->validate('user@example.com');

if ($result->valid) {
    echo "✅ Valid email!\n";
    echo "Score: {$result->score}\n";
    echo "Quality: {$result->qualityGrade}\n";
} else {
    echo "❌ Invalid email\n";
    echo "Reason: {$result->reason}\n";
}
```

### Validate Multiple Emails

```php
<?php

$emails = [
    'john@gmail.com',
    'jane@company.com',
    'invalid@email'
];

$result = $client->validateBulk($emails);

echo "Valid: {$result->summary->valid}\n";
echo "Invalid: {$result->summary->invalid}\n";

foreach ($result->results as $email) {
    $status = $email->valid ? '✅' : '❌';
    echo "{$status} {$email->email}\n";
}
```

### Check for Typos

```php
<?php

$result = $client->validate('user@gmial.com'); // Note the typo

if ($result->didYouMean) {
    echo "Did you mean: {$result->didYouMean}?\n";
    // Suggest: user@gmail.com
}
```

## 4. Error Handling

```php
<?php

use VerifyKit\Exception\ValidationException;
use VerifyKit\Exception\RateLimitException;
use VerifyKit\Exception\QuotaExceededException;

try {
    $result = $client->validate('user@example.com');
} catch (ValidationException $e) {
    echo "Validation error: {$e->getMessage()}\n";
} catch (RateLimitException $e) {
    echo "Rate limit exceeded, retry after {$e->retryAfter}s\n";
} catch (QuotaExceededException $e) {
    echo "Monthly quota exceeded\n";
}
```

## 5. Check Your Usage

```php
<?php

$usage = $client->getUsage();

echo "Used: {$usage->current}/{$usage->limit}\n";
echo "Remaining: {$usage->remaining}\n";
echo "Usage: {$usage->percentage}%\n";
```

## Common Use Cases

### 1. Form Validation

```php
<?php

function validateRegistrationEmail(string $email): array
{
    $client = new VerifyKit(apiKey: $_ENV['VERIFYKIT_API_KEY']);

    try {
        $result = $client->validate($email);

        if (!$result->valid) {
            return ['valid' => false, 'error' => 'Invalid email address'];
        }

        if ($result->disposable) {
            return ['valid' => false, 'error' => 'Disposable emails are not allowed'];
        }

        if ($result->didYouMean) {
            return [
                'valid' => true,
                'suggestion' => $result->didYouMean,
                'message' => "Did you mean {$result->didYouMean}?"
            ];
        }

        return ['valid' => true];

    } catch (\Exception $e) {
        return ['valid' => false, 'error' => 'Unable to validate email'];
    }
}
```

### 2. Mailing List Cleanup

```php
<?php

function cleanMailingList(array $emails): array
{
    $client = new VerifyKit(apiKey: $_ENV['VERIFYKIT_API_KEY']);

    $result = $client->validateBulk($emails);

    $valid = [];
    $invalid = [];
    $risky = [];

    foreach ($result->results as $email) {
        if ($email->valid && !$email->disposable) {
            $valid[] = $email->email;
        } elseif ($email->reachable === 'risky') {
            $risky[] = $email->email;
        } else {
            $invalid[] = $email->email;
        }
    }

    return [
        'valid' => $valid,
        'risky' => $risky,
        'invalid' => $invalid,
        'stats' => [
            'total' => $result->summary->total,
            'valid_count' => count($valid),
            'risky_count' => count($risky),
            'invalid_count' => count($invalid),
        ]
    ];
}
```

### 3. Real-time Validation API

```php
<?php

// Laravel/Symfony example
class EmailController
{
    private VerifyKit $verifyKit;

    public function __construct()
    {
        $this->verifyKit = new VerifyKit(
            apiKey: $_ENV['VERIFYKIT_API_KEY']
        );
    }

    public function validate(Request $request): JsonResponse
    {
        $email = $request->input('email');

        try {
            $result = $this->verifyKit->validate($email);

            return response()->json([
                'valid' => $result->valid,
                'score' => $result->score,
                'quality' => $result->qualityGrade,
                'disposable' => $result->disposable,
                'did_you_mean' => $result->didYouMean,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
```

## Configuration Options

```php
<?php

$client = new VerifyKit(
    apiKey: $_ENV['VERIFYKIT_API_KEY'],
    timeout: 30,           // Request timeout in seconds
    maxRetries: 3,         // Max retry attempts
    debug: false,          // Enable debug logging
    headers: []            // Custom headers
);
```

## Next Steps

- 📖 Read the full [README.md](./README.md)
- 🔍 Check out the [examples](./examples) directory
- 📚 Visit the [API Documentation](https://verifykit.io/docs)
- 💬 Get support at [support@verifykit.io](mailto:support@verifykit.io)

## Tips

- ✅ Use environment variables for API keys
- ✅ Enable debug mode during development
- ✅ Handle rate limits gracefully
- ✅ Check for disposable emails in registration forms
- ✅ Use bulk validation for large lists (up to 1,000 emails)
- ✅ Monitor your usage with `getUsage()`
- ✅ Implement retry logic for failed requests

## Support

Need help? Contact us at [support@verifykit.io](mailto:support@verifykit.io)
