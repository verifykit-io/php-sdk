<?php

declare(strict_types=1);

namespace VerifyKit\Exception;

/**
 * Configuration or validation error (4xx client errors)
 */
class ValidationException extends VerifyKitException
{
    /** @var array<int, array{field: string, message: string}>|null */
    public ?array $details = null;

    public function __construct(
        string $message,
        ?string $code = null,
        ?int $statusCode = null,
        ?array $details = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $statusCode, null, null, $previous);
        $this->details = $details;
    }
}
