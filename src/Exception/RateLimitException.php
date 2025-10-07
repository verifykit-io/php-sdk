<?php

declare(strict_types=1);

namespace VerifyKit\Exception;

/**
 * Rate limit exceeded error (429 Too Many Requests)
 */
class RateLimitException extends VerifyKitException
{
    public ?int $retryAfter = null;
    public ?int $limit = null;
    public ?int $remaining = null;

    public function __construct(
        string $message = 'Rate limit exceeded',
        ?string $code = null,
        ?int $retryAfter = null,
        ?int $limit = null,
        ?int $remaining = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, 429, null, null, $previous);
        $this->retryAfter = $retryAfter;
        $this->limit = $limit;
        $this->remaining = $remaining;
    }
}
