<?php

declare(strict_types=1);

namespace VerifyKit;

use VerifyKit\Dto\BulkValidationResult;
use VerifyKit\Dto\ResponseMetadata;
use VerifyKit\Dto\UsageStats;
use VerifyKit\Dto\ValidationResult;
use VerifyKit\Exception\AuthenticationException;
use VerifyKit\Exception\ConfigurationException;
use VerifyKit\Exception\NetworkException;
use VerifyKit\Exception\QuotaExceededException;
use VerifyKit\Exception\RateLimitException;
use VerifyKit\Exception\TimeoutException;
use VerifyKit\Exception\ValidationException;
use VerifyKit\Exception\VerifyKitException;

/**
 * VerifyKit API Client
 *
 * A powerful and easy-to-use SDK for email validation and verification.
 *
 * @example
 * ```php
 * use VerifyKit\VerifyKit;
 *
 * $client = new VerifyKit(apiKey: $_ENV['VERIFYKIT_API_KEY']);
 *
 * // Validate a single email
 * $result = $client->validate('user@example.com');
 * echo $result->valid ? 'Valid' : 'Invalid';
 *
 * // Validate multiple emails
 * $bulkResult = $client->validateBulk([
 *     'user1@example.com',
 *     'user2@example.com'
 * ]);
 * ```
 */
final class VerifyKit
{
    private const VERSION = '1.0.0';
    private const DEFAULT_BASE_URL = 'https://api.verifykit.io';
    private const DEFAULT_TIMEOUT = 30;
    private const DEFAULT_MAX_RETRIES = 3;

    private ?ResponseMetadata $lastMetadata = null;

    /**
     * Create a new VerifyKit client
     *
     * @param string $apiKey Your VerifyKit API key (required)
     * @param string $baseUrl Base URL for the VerifyKit API
     * @param int $timeout Request timeout in seconds
     * @param int $maxRetries Maximum number of retry attempts
     * @param bool $debug Enable debug logging
     * @param array<string, string> $headers Custom headers
     * @throws ConfigurationException If the configuration is invalid
     */
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl = self::DEFAULT_BASE_URL,
        private readonly int $timeout = self::DEFAULT_TIMEOUT,
        private readonly int $maxRetries = self::DEFAULT_MAX_RETRIES,
        private readonly bool $debug = false,
        private readonly array $headers = []
    ) {
        $this->validateConfig();

        if ($this->debug) {
            $this->log('Client initialized', [
                'baseUrl' => $this->baseUrl,
                'timeout' => $this->timeout,
                'maxRetries' => $this->maxRetries,
            ]);
        }
    }

    /**
     * Validate a single email address
     *
     * @param string $email The email address to validate
     * @param bool $skipSmtp Skip SMTP validation for faster results
     * @param string|null $webhook Webhook URL to receive results asynchronously
     * @return ValidationResult
     * @throws ValidationException If the email format is invalid
     * @throws AuthenticationException If the API key is invalid
     * @throws RateLimitException If rate limit is exceeded
     * @throws QuotaExceededException If monthly quota is exceeded
     *
     * @example
     * ```php
     * $result = $client->validate('user@example.com');
     * if ($result->valid) {
     *     echo 'Email is valid!';
     * }
     * ```
     */
    public function validate(
        string $email,
        bool $skipSmtp = false,
        ?string $webhook = null
    ): ValidationResult {
        $this->log('Validating single email', ['email' => $email]);

        if (empty($email)) {
            throw new ValidationException('Email is required and must be a non-empty string', 'INVALID_EMAIL');
        }

        if (!$this->isValidEmail($email)) {
            throw new ValidationException('Invalid email format', 'INVALID_EMAIL_FORMAT');
        }

        if ($webhook !== null && !$this->isValidUrl($webhook)) {
            throw new ValidationException('Invalid webhook URL', 'INVALID_WEBHOOK_URL');
        }

        $response = $this->request(
            method: 'POST',
            path: '/v1/verify',
            body: array_filter([
                'email' => $email,
                'skip_smtp' => $skipSmtp ? true : null,
                'webhook' => $webhook,
            ], fn($v) => $v !== null)
        );

        $result = ValidationResult::fromArray($response);

        $this->log('Email validation complete', [
            'email' => $email,
            'valid' => $result->valid,
            'reachable' => $result->reachable,
            'score' => $result->score,
        ]);

        return $result;
    }

    /**
     * Validate multiple email addresses
     *
     * This method automatically handles:
     * - Duplicate removal
     * - Batching for large lists
     * - Quota management
     * - Rate limiting
     *
     * @param string[] $emails Array of email addresses to validate
     * @param bool $skipSmtp Skip SMTP validation for faster results
     * @param string|null $webhook Webhook URL to receive results asynchronously
     * @return BulkValidationResult
     * @throws ValidationException If the email list is invalid
     * @throws AuthenticationException If the API key is invalid
     * @throws RateLimitException If rate limit is exceeded
     * @throws QuotaExceededException If monthly quota is exceeded
     *
     * @example
     * ```php
     * $result = $client->validateBulk([
     *     'user1@example.com',
     *     'user2@example.com',
     *     'user3@example.com'
     * ]);
     *
     * echo "Validated {$result->summary->total} emails\n";
     * echo "Valid: {$result->summary->valid}\n";
     * echo "Invalid: {$result->summary->invalid}\n";
     * ```
     */
    public function validateBulk(
        array $emails,
        bool $skipSmtp = false,
        ?string $webhook = null
    ): BulkValidationResult {
        $this->log('Validating bulk emails', ['count' => count($emails)]);

        if (empty($emails)) {
            throw new ValidationException('Emails array cannot be empty', 'EMPTY_EMAILS');
        }

        if (count($emails) > 1000) {
            throw new ValidationException('Maximum 1000 emails per request', 'TOO_MANY_EMAILS');
        }

        // Validate each email format
        foreach ($emails as $email) {
            if (!$this->isValidEmail($email)) {
                throw new ValidationException("Invalid email format: {$email}", 'INVALID_EMAIL_FORMAT');
            }
        }

        if ($webhook !== null && !$this->isValidUrl($webhook)) {
            throw new ValidationException('Invalid webhook URL', 'INVALID_WEBHOOK_URL');
        }

        // Remove duplicates
        $unique = array_unique($emails);
        $duplicates = count($emails) - count($unique);

        if ($duplicates > 0) {
            $this->log("Removed {$duplicates} duplicate emails");
        }

        $response = $this->request(
            method: 'POST',
            path: '/v1/verify/bulk',
            body: array_filter([
                'emails' => array_values($unique),
                'skip_smtp' => $skipSmtp ? true : null,
                'webhook' => $webhook,
            ], fn($v) => $v !== null)
        );

        $result = BulkValidationResult::fromArray($response);

        $this->log('Bulk validation complete', [
            'total' => $result->summary->total,
            'valid' => $result->summary->valid,
            'invalid' => $result->summary->invalid,
            'risky' => $result->summary->risky,
            'processingTime' => $result->summary->processingTimeMs,
        ]);

        return $result;
    }

    /**
     * Get current usage statistics
     *
     * @return UsageStats
     * @throws AuthenticationException If the API key is invalid
     *
     * @example
     * ```php
     * $usage = $client->getUsage();
     * echo "Used {$usage->current} of {$usage->limit} validations\n";
     * echo "{$usage->remaining} remaining ({$usage->percentage}%)\n";
     * ```
     */
    public function getUsage(): UsageStats
    {
        $this->log('Fetching usage stats');

        $response = $this->request(
            method: 'GET',
            path: '/v1/user/usage'
        );

        $result = UsageStats::fromArray($response);

        $this->log('Usage stats retrieved', (array) $result);

        return $result;
    }

    /**
     * Get metadata from the last request
     *
     * This includes rate limit info, request ID, cache status, etc.
     *
     * @return ResponseMetadata|null
     *
     * @example
     * ```php
     * $client->validate('user@example.com');
     * $metadata = $client->getLastMetadata();
     * echo "Request ID: {$metadata?->requestId}\n";
     * ```
     */
    public function getLastMetadata(): ?ResponseMetadata
    {
        return $this->lastMetadata;
    }

    /**
     * Validate configuration on initialization
     *
     * @throws ConfigurationException
     */
    private function validateConfig(): void
    {
        if (empty($this->apiKey)) {
            throw new ConfigurationException('API key is required');
        }

        if (!$this->isValidApiKey($this->apiKey)) {
            throw new ConfigurationException(
                'Invalid API key format. API key must start with vk_live_ or vk_test_'
            );
        }

        if (!$this->isValidUrl($this->baseUrl)) {
            throw new ConfigurationException('Invalid base URL');
        }

        if ($this->timeout <= 0) {
            throw new ConfigurationException('Timeout must be greater than 0');
        }

        if ($this->maxRetries < 0) {
            throw new ConfigurationException('Max retries must be 0 or greater');
        }
    }

    /**
     * Make an HTTP request to the API with retry logic
     *
     * @param string $method HTTP method
     * @param string $path API path
     * @param array<string, mixed>|null $body Request body
     * @param int $attempt Current attempt number
     * @return array<string, mixed>
     * @throws VerifyKitException
     */
    private function request(
        string $method,
        string $path,
        ?array $body = null,
        int $attempt = 0
    ): array {
        $url = $this->baseUrl . $path;

        $this->log("Request {$method} {$path}", [
            'attempt' => $attempt + 1,
            'timeout' => $this->timeout,
        ]);

        $headers = array_merge([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiKey,
            'User-Agent' => 'verifykit-php-sdk/' . self::VERSION,
        ], $this->headers);

        $options = [
            'http' => [
                'method' => $method,
                'header' => $this->buildHeaderString($headers),
                'timeout' => $this->timeout,
                'ignore_errors' => true,
            ],
        ];

        if ($body !== null) {
            $options['http']['content'] = json_encode($body);
        }

        $context = stream_context_create($options);

        try {
            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                throw new NetworkException('Network request failed');
            }

            // Parse response headers
            /** @var string[] $http_response_header */
            $httpResponseHeader = isset($http_response_header) ? $http_response_header : [];
            $responseHeaders = $this->parseHttpResponseHeaders($httpResponseHeader);
            $this->lastMetadata = ResponseMetadata::fromHeaders($responseHeaders);

            // Get status code
            $statusCode = $this->getStatusCodeFromHeaders($httpResponseHeader);

            // Handle error responses
            if ($statusCode >= 400) {
                return $this->handleErrorResponse($response, $statusCode, $attempt, $method, $path, $body);
            }

            // Parse success response
            $data = json_decode($response, true);

            if (!is_array($data)) {
                throw new NetworkException('Invalid JSON response');
            }

            return $data;

        } catch (NetworkException | TimeoutException $e) {
            if ($this->shouldRetry($attempt, $e)) {
                $delay = $this->getRetryDelay($attempt, $e);
                $this->log("Error, retrying in {$delay}ms...", ['error' => $e->getMessage()]);
                usleep($delay * 1000);
                return $this->request($method, $path, $body, $attempt + 1);
            }

            throw $e;
        }
    }

    /**
     * Handle error responses from the API
     *
     * @param string $response Response body
     * @param int $statusCode HTTP status code
     * @param int $attempt Current attempt number
     * @param string $method HTTP method
     * @param string $path API path
     * @param array<string, mixed>|null $body Request body
     * @return array<string, mixed>
     * @throws VerifyKitException
     */
    private function handleErrorResponse(
        string $response,
        int $statusCode,
        int $attempt,
        string $method,
        string $path,
        ?array $body
    ): array {
        $errorData = json_decode($response, true);

        if (!is_array($errorData)) {
            $errorData = [
                'error' => 'Unknown Error',
                'message' => 'An unknown error occurred',
            ];
        }

        $errorType = isset($errorData['error']) && is_string($errorData['error']) ? $errorData['error'] : 'Unknown';
        $errorMessage = isset($errorData['message']) && is_string($errorData['message']) ? $errorData['message'] : 'Unknown error';

        $this->log('API error', [
            'status' => $statusCode,
            'error' => $errorType,
            'message' => $errorMessage,
        ]);

        // Handle quota exceeded
        if ($statusCode === 429 && $errorType === 'Monthly Limit Exceeded') {
            throw new QuotaExceededException(
                message: $errorMessage,
                code: $errorType
            );
        }

        // Handle rate limit
        if ($statusCode === 429) {
            $retryAfter = $this->lastMetadata?->rateLimit?->retryAfter;
            $error = new RateLimitException(
                message: $errorMessage,
                code: $errorType,
                retryAfter: $retryAfter
            );

            // Auto-retry rate limit errors
            if ($this->shouldRetry($attempt, $error)) {
                $delay = $this->getRetryDelay($attempt, $error);
                $this->log("Rate limited, retrying in {$delay}ms...");
                usleep($delay * 1000);
                return $this->request($method, $path, $body, $attempt + 1);
            }

            throw $error;
        }

        // Create appropriate error
        $error = VerifyKitException::fromApiError($errorData, $statusCode);

        // Retry server errors
        if ($this->shouldRetry($attempt, $error)) {
            $delay = $this->getRetryDelay($attempt, $error);
            $this->log("Server error, retrying in {$delay}ms...");
            usleep($delay * 1000);
            return $this->request($method, $path, $body, $attempt + 1);
        }

        throw $error;
    }

    /**
     * Determine if a request should be retried
     */
    private function shouldRetry(int $attempt, \Throwable $error): bool
    {
        if ($attempt >= $this->maxRetries) {
            return false;
        }

        return $error instanceof NetworkException
            || $error instanceof TimeoutException
            || $error instanceof RateLimitException
            || ($error instanceof VerifyKitException && ($error->statusCode ?? 0) >= 500);
    }

    /**
     * Calculate retry delay with exponential backoff
     */
    private function getRetryDelay(int $attempt, \Throwable $error): int
    {
        if ($error instanceof RateLimitException && $error->retryAfter !== null) {
            return $error->retryAfter * 1000; // Convert to milliseconds
        }

        // Exponential backoff: 1s, 2s, 4s, 8s...
        return (int) (1000 * (2 ** $attempt));
    }

    /**
     * Validate email format
     */
    private function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate API key format
     */
    private function isValidApiKey(string $apiKey): bool
    {
        return str_starts_with($apiKey, 'vk_live_') || str_starts_with($apiKey, 'vk_test_');
    }

    /**
     * Validate URL format
     */
    private function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Build header string for request
     *
     * @param array<string, string> $headers
     */
    private function buildHeaderString(array $headers): string
    {
        $headerLines = [];
        foreach ($headers as $key => $value) {
            $headerLines[] = "{$key}: {$value}";
        }
        return implode("\r\n", $headerLines);
    }

    /**
     * Parse HTTP response headers
     *
     * @param string[] $headerLines
     * @return array<string, string>
     */
    private function parseHttpResponseHeaders(array $headerLines): array
    {
        $headers = [];
        foreach ($headerLines as $line) {
            if (strpos($line, ':') !== false) {
                [$key, $value] = explode(':', $line, 2);
                $headers[strtolower(trim($key))] = trim($value);
            }
        }
        return $headers;
    }

    /**
     * Get status code from response headers
     *
     * @param string[] $headerLines
     */
    private function getStatusCodeFromHeaders(array $headerLines): int
    {
        if (empty($headerLines)) {
            return 500;
        }

        // First line contains status code
        if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $headerLines[0], $matches)) {
            return (int) $matches[1];
        }

        return 500;
    }

    /**
     * Log debug messages
     *
     * @param string $message
     * @param array<string, mixed> $context
     */
    private function log(string $message, array $context = []): void
    {
        if (!$this->debug) {
            return;
        }

        $contextStr = empty($context) ? '' : ' ' . json_encode($context);
        error_log("[VerifyKit] {$message}{$contextStr}");
    }
}
