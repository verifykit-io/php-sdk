<?php

declare(strict_types=1);

namespace VerifyKit\Exception;

/**
 * Request timeout error
 */
class TimeoutException extends VerifyKitException
{
    public ?int $timeout = null;

    public function __construct(
        string $message = 'Request timeout',
        ?int $timeout = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 'TIMEOUT', null, null, null, $previous);
        $this->timeout = $timeout;
    }
}
