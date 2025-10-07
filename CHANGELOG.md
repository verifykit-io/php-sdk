# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-10-07

### Added
- Initial release of VerifyKit PHP SDK
- Single email validation with `validate()` method
- Bulk email validation with `validateBulk()` method (up to 1,000 emails)
- Usage statistics tracking with `getUsage()` method
- Request metadata retrieval with `getLastMetadata()` method
- Automatic retry logic with exponential backoff
- Rate limit handling with automatic retries
- Comprehensive exception classes for error handling:
  - `VerifyKitException` - Base exception class
  - `ValidationException` - Validation errors
  - `AuthenticationException` - Authentication errors
  - `RateLimitException` - Rate limit errors
  - `QuotaExceededException` - Quota exceeded errors
  - `ServerException` - Server errors
  - `NetworkException` - Network errors
  - `TimeoutException` - Timeout errors
  - `ConfigurationException` - Configuration errors
- Response DTOs with readonly properties:
  - `ValidationResult` - Single email validation result
  - `BulkValidationResult` - Bulk validation result
  - `BulkValidationSummary` - Bulk validation summary
  - `UsageStats` - Usage statistics
  - `ResponseMetadata` - Request metadata
  - `RateLimitInfo` - Rate limit information
  - `SyntaxValidation` - Email syntax validation
  - `MxValidation` - MX record validation
  - `SmtpValidation` - SMTP validation
- Configuration options:
  - Custom base URL
  - Request timeout
  - Maximum retries
  - Debug logging
  - Custom headers
- Comprehensive test suite using Pest PHP
- Example files demonstrating common use cases:
  - Basic usage
  - Bulk validation
  - Typo detection
  - Error handling
- Full documentation in README.md
- PHP 7.4+ support with PHP 8.0+ features
- PSR-4 autoloading
- Packagist/Composer support

### Features
- Email typo detection with "did you mean" suggestions
- Disposable email detection
- Role-based email detection
- Free email provider detection
- Email quality scoring (0-1)
- Quality grading (excellent, good, fair, poor)
- MX record validation
- SMTP validation (optional, can be skipped for faster results)
- Syntax validation with username/domain parsing
- Automatic duplicate email removal in bulk validation
- Quota management in bulk validation
- Cache status tracking
- Response time tracking

[1.0.0]: https://github.com/verifykit/verifykit/releases/tag/php-sdk-v1.0.0
