<?php

declare(strict_types=1);

namespace VerifyKit\Exception;

/**
 * Authentication error (401 Unauthorized)
 */
class AuthenticationException extends VerifyKitException
{
    public function __construct(
        string $message = 'Invalid API key or authentication required',
        ?string $code = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, 401, null, null, $previous);
    }
}
