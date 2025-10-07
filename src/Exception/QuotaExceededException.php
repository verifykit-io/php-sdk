<?php

declare(strict_types=1);

namespace VerifyKit\Exception;

/**
 * Monthly quota exceeded error
 */
class QuotaExceededException extends VerifyKitException
{
    public ?int $currentUsage = null;
    public ?int $monthlyLimit = null;
    public ?string $upgradeUrl = null;

    public function __construct(
        string $message = 'Monthly validation quota exceeded',
        ?string $code = null,
        ?int $currentUsage = null,
        ?int $monthlyLimit = null,
        ?string $upgradeUrl = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, 429, null, null, $previous);
        $this->currentUsage = $currentUsage;
        $this->monthlyLimit = $monthlyLimit;
        $this->upgradeUrl = $upgradeUrl;
    }
}
