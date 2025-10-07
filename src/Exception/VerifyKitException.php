<?php

declare(strict_types=1);

namespace VerifyKit\Exception;

use Exception;

/**
 * Base exception class for all VerifyKit errors
 */
class VerifyKitException extends Exception
{
    public ?string $errorCode = null;
    public ?int $statusCode = null;
    public ?string $requestId = null;
    public ?string $documentation = null;

    public function __construct(
        string $message,
        ?string $code = null,
        ?int $statusCode = null,
        ?string $requestId = null,
        ?string $documentation = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->errorCode = $code;
        $this->statusCode = $statusCode;
        $this->requestId = $requestId;
        $this->documentation = $documentation;
    }

    /**
     * Create a VerifyKitException from an API error response
     *
     * @param array<string, mixed> $response
     */
    public static function fromApiError(array $response, int $statusCode): VerifyKitException
    {
        $errorClass = self::getErrorClassForStatus($statusCode);
        $message = $response['message'] ?? 'Unknown error';
        $code = isset($response['error']) && is_string($response['error']) ? $response['error'] : null;

        /** @var VerifyKitException $error */
        $error = new $errorClass($message, $code, $statusCode);
        $error->requestId = isset($response['requestId']) && is_string($response['requestId']) ? $response['requestId'] : null;
        $error->documentation = isset($response['documentation']) && is_string($response['documentation']) ? $response['documentation'] : null;

        return $error;
    }

    /**
     * Get the appropriate error class for an HTTP status code
     */
    private static function getErrorClassForStatus(int $statusCode): string
    {
        return match (true) {
            $statusCode === 401 => AuthenticationException::class,
            $statusCode === 404 => NotFoundException::class,
            $statusCode === 429 => RateLimitException::class,
            $statusCode >= 400 && $statusCode < 500 => ValidationException::class,
            $statusCode >= 500 => ServerException::class,
            default => self::class,
        };
    }
}
