<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use VerifyKit\VerifyKit;

/**
 * Email Typo Detection Example
 *
 * This example demonstrates the "did you mean" feature for detecting
 * and correcting common email typos.
 */

// Initialize the client
$client = new VerifyKit(
    apiKey: $_ENV['VERIFYKIT_API_KEY'] ?? 'vk_test_your_api_key_here'
);

// Common typos to test
$typos = [
    'user@gmial.com',      // Should suggest gmail.com
    'user@gmai.com',       // Should suggest gmail.com
    'user@hotmial.com',    // Should suggest hotmail.com
    'user@yaho.com',       // Should suggest yahoo.com
    'user@outlok.com',     // Should suggest outlook.com
];

echo "=== Email Typo Detection ===\n\n";

foreach ($typos as $email) {
    try {
        echo "Testing: {$email}\n";

        $result = $client->validate($email);

        if ($result->didYouMean) {
            echo "✓ Typo detected!\n";
            echo "  Suggested correction: {$result->didYouMean}\n";

            // Validate the suggested email
            echo "  Validating suggestion...\n";
            $correctedResult = $client->validate($result->didYouMean);

            echo "  Corrected email is: " . ($correctedResult->valid ? 'Valid ✓' : 'Invalid ✗') . "\n";
            echo "  Reachable: {$correctedResult->reachable}\n";
            echo "  Score: {$correctedResult->score}\n";
        } else {
            echo "  No typo detected\n";
            echo "  Valid: " . ($result->valid ? 'Yes' : 'No') . "\n";
        }

        echo "\n";

    } catch (\Exception $e) {
        echo "Error: {$e->getMessage()}\n\n";
    }
}

echo "=== Interactive Typo Correction Example ===\n\n";

// Simulate user input with a typo
$userInput = 'john.doe@gmial.com';

try {
    $result = $client->validate($userInput);

    if ($result->didYouMean) {
        // In a real application, you would prompt the user here
        echo "User entered: {$userInput}\n";
        echo "We detected a possible typo.\n";
        echo "Did you mean: {$result->didYouMean}? (yes/no)\n";

        // Simulate user confirming the correction
        $userConfirms = true;

        if ($userConfirms) {
            echo "User confirmed! Using: {$result->didYouMean}\n";
            $finalEmail = $result->didYouMean;
        } else {
            echo "User declined. Using original: {$userInput}\n";
            $finalEmail = $userInput;
        }

        echo "\nFinal email to use: {$finalEmail}\n";
    }

} catch (\Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}
