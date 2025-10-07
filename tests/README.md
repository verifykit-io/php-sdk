# VerifyKit PHP SDK Tests

This directory contains comprehensive tests for the VerifyKit PHP SDK.

## Test Structure

```
tests/
├── Unit/                           # Unit tests (no API calls)
│   ├── VerifyKitTest.php           # Client configuration and validation tests
│   ├── DtoTest.php                 # DTO creation and data mapping tests
│   └── ExceptionTest.php           # Exception handling tests
├── Integration/                    # Integration tests (real API calls)
│   ├── VerifyKitIntegrationTest.php       # Main API functionality tests
│   ├── ErrorHandlingIntegrationTest.php   # Error scenarios
│   ├── RetryLogicIntegrationTest.php      # Retry and resilience tests
│   └── PerformanceIntegrationTest.php     # Performance benchmarks
├── Pest.php                        # Pest configuration and helpers
└── TestCase.php                    # Base test case
```

## Running Tests

### All Tests

```bash
composer test
```

### Unit Tests Only (No API Key Required)

```bash
./vendor/bin/pest tests/Unit
```

### Integration Tests (Requires API Key)

```bash
# Set your API key
export VERIFYKIT_API_KEY=vk_test_your_test_key_here

# Run all integration tests
./vendor/bin/pest --group=integration

# Run specific integration test file
./vendor/bin/pest tests/Integration/VerifyKitIntegrationTest.php
```

### Performance Tests (Slower Tests)

```bash
./vendor/bin/pest --group=performance
```

### With Coverage

```bash
composer test:coverage
```

### Watch Mode

```bash
./vendor/bin/pest --watch
```

## Test Groups

Tests are organized using Pest groups:

- `integration` - All integration tests that make real API calls
- `performance` - Performance and load tests
- `slow` - Tests that take longer to run

### Run Specific Groups

```bash
# Run only integration tests
./vendor/bin/pest --group=integration

# Run only performance tests
./vendor/bin/pest --group=performance

# Exclude slow tests
./vendor/bin/pest --exclude-group=slow
```

## Integration Tests

Integration tests make **real API calls** to the VerifyKit API. They require:

1. **Valid API Key**: Set `VERIFYKIT_API_KEY` environment variable
2. **Internet Connection**: Tests make HTTP requests
3. **API Quota**: Tests consume your API quota

### What Integration Tests Cover

- ✅ Single email validation
- ✅ Bulk email validation (3-100 emails)
- ✅ Typo detection ("did you mean" feature)
- ✅ Disposable email detection
- ✅ Role-based email detection
- ✅ Usage statistics retrieval
- ✅ Request metadata tracking
- ✅ SMTP validation (with skip option)
- ✅ Duplicate email removal
- ✅ Error handling with real API responses
- ✅ Rate limit handling
- ✅ Retry logic
- ✅ Performance benchmarks

### Integration Test Examples

```bash
# Test single email validation
./vendor/bin/pest --filter="can validate a single email successfully"

# Test bulk validation
./vendor/bin/pest --filter="can validate multiple emails in bulk"

# Test typo detection
./vendor/bin/pest --filter="can detect email typos"

# Test error handling
./vendor/bin/pest tests/Integration/ErrorHandlingIntegrationTest.php

# Test performance
./vendor/bin/pest tests/Integration/PerformanceIntegrationTest.php
```

## Unit Tests

Unit tests **do not make API calls** and test:

- Client initialization
- Configuration validation
- Input validation
- DTO creation from arrays
- Exception creation
- Edge cases

Unit tests run fast and don't require an API key.

## Environment Setup

### For Integration Tests

Create a `.env` file in the SDK root:

```bash
VERIFYKIT_API_KEY=vk_test_your_test_key_here
```

Or export the variable:

```bash
export VERIFYKIT_API_KEY=vk_test_your_test_key_here
```

### Using Test API Key

If you have a test API key (`vk_test_...`), integration tests will use the test environment.

## Test Helpers

The `tests/Pest.php` file provides helper functions:

```php
getTestApiKey()              // Returns a valid test API key format
getMockValidationResponse()  // Returns mock validation data
```

## Continuous Integration

For CI/CD pipelines:

```bash
# Run only unit tests (fast, no API key needed)
./vendor/bin/pest tests/Unit

# Run with coverage for reporting
./vendor/bin/pest --coverage --min=80

# Run all tests including integration (requires API key in CI secrets)
VERIFYKIT_API_KEY=$API_KEY ./vendor/bin/pest
```

## Writing New Tests

### Unit Test Example

```php
test('validates configuration parameter', function () {
    $client = new VerifyKit(
        apiKey: getTestApiKey(),
        timeout: 30
    );

    expect($client)->toBeInstanceOf(VerifyKit::class);
});
```

### Integration Test Example

```php
test('validates real email address', function () {
    $apiKey = $_ENV['VERIFYKIT_API_KEY'] ?? null;

    if (!$apiKey) {
        test()->markTestSkipped('VERIFYKIT_API_KEY not set');
    }

    $client = new VerifyKit(apiKey: $apiKey);
    $result = $client->validate('test@example.com');

    expect($result->valid)->toBeBool();
})->group('integration');
```

## Test Coverage

Current test coverage includes:

- **Client Class**: Configuration, validation, bulk validation, usage stats
- **DTOs**: All data transfer objects with fromArray() methods
- **Exceptions**: All exception types with proper properties
- **Integration**: Real API calls for all major features
- **Error Handling**: Configuration errors, validation errors, API errors
- **Performance**: Speed benchmarks and load tests

### Coverage Report

Generate HTML coverage report:

```bash
composer test:coverage
open coverage/html/index.html
```

## Tips for Running Tests

1. **Start with Unit Tests**: Fast feedback without API calls
2. **Use Test API Key**: Avoid consuming production quota
3. **Skip Slow Tests**: Use `--exclude-group=slow` for quick iterations
4. **Use Filters**: Run specific tests with `--filter="test name"`
5. **Watch Mode**: Use `--watch` for TDD workflow
6. **CI/CD**: Run unit tests on every commit, integration tests on deploy

## Common Issues

### "VERIFYKIT_API_KEY not set"

Integration tests are skipped if the API key is not set. This is intentional - unit tests will still run.

**Solution**: Export the environment variable before running tests.

### "Rate limit exceeded"

If you hit rate limits during testing, the SDK will automatically retry. For development:

- Use a test API key with higher limits
- Reduce the number of integration test runs
- Use `--filter` to run specific tests

### "Tests are slow"

Performance tests intentionally make multiple API calls. To skip them:

```bash
./vendor/bin/pest --exclude-group=slow --exclude-group=performance
```

## Resources

- [Pest Documentation](https://pestphp.com/)
- [VerifyKit API Docs](https://verifykit.io/docs)
- [PHPUnit Documentation](https://phpunit.de/)
