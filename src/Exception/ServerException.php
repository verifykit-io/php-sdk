<?php

declare(strict_types=1);

namespace VerifyKit\Exception;

/**
 * Server error (5xx)
 */
class ServerException extends VerifyKitException
{
    public function __construct(
        string $message = 'Internal server error',
        ?string $code = null,
        int $statusCode = 500,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $statusCode, null, null, $previous);
    }
}
