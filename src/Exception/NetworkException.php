<?php

declare(strict_types=1);

namespace VerifyKit\Exception;

/**
 * Network or connection error
 */
class NetworkException extends VerifyKitException
{
    public function __construct(
        string $message = 'Network request failed',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 'NETWORK_ERROR', null, null, null, $previous);
    }
}
