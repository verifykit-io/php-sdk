<?php

declare(strict_types=1);

namespace VerifyKit\Exception;

/**
 * Resource not found error (404 Not Found)
 */
class NotFoundException extends VerifyKitException
{
    public function __construct(
        string $message = 'Resource not found',
        ?string $code = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, 404, null, null, $previous);
    }
}
