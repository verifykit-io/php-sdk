<?php

declare(strict_types=1);

namespace VerifyKit\Exception;

/**
 * Invalid configuration error
 */
class ConfigurationException extends VerifyKitException
{
    public function __construct(
        string $message,
        ?string $code = 'INVALID_CONFIG',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, null, null, null, $previous);
    }
}
