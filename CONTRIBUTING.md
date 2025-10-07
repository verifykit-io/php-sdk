# Contributing to VerifyKit PHP SDK

Thank you for your interest in contributing to the VerifyKit PHP SDK! This document provides guidelines and instructions for contributing.

## Code of Conduct

By participating in this project, you agree to maintain a respectful and inclusive environment for all contributors.

## Getting Started

### Prerequisites

- PHP 8.0 or higher (for development)
- Composer
- Git

### Setup Development Environment

1. Fork the repository
2. Clone your fork:
   ```bash
   git clone https://github.com/YOUR_USERNAME/verifykit.git
   cd verifykit/packages/sdk-php
   ```

3. Install dependencies:
   ```bash
   composer install
   ```

4. Create a `.env` file with your test API key:
   ```bash
   VERIFYKIT_API_KEY=vk_test_your_test_key_here
   ```

## Development Workflow

### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
composer test:coverage

# Watch mode (requires pest --watch)
./vendor/bin/pest --watch
```

### Code Quality

```bash
# Run static analysis
composer analyse

# Format code
composer format

# Check code formatting without changes
composer format:check
```

### Before Submitting

1. Ensure all tests pass
2. Run static analysis (PHPStan level max)
3. Format your code with PHP CS Fixer
4. Update documentation if needed
5. Add tests for new features
6. Update CHANGELOG.md

## Pull Request Process

1. Create a feature branch:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Make your changes with clear commit messages:
   ```bash
   git commit -m "Add feature: description"
   ```

3. Push to your fork:
   ```bash
   git push origin feature/your-feature-name
   ```

4. Create a Pull Request with:
   - Clear description of changes
   - Link to related issues
   - Screenshots if applicable
   - Test results

## Coding Standards

### PHP Standards

- Follow PSR-12 coding standards
- Use PHP 8 features (typed properties, named arguments, match expressions)
- Maintain PHP 7.4 compatibility where possible
- Use strict types: `declare(strict_types=1);`
- Use readonly properties for immutable data
- Use final classes unless inheritance is intentional

### Documentation

- Add PHPDoc blocks for all public methods
- Include usage examples in documentation
- Update README.md for new features
- Add inline comments for complex logic

### Testing

- Write tests for all new features
- Maintain test coverage above 80%
- Use Pest PHP testing framework
- Follow Arrange-Act-Assert pattern
- Use descriptive test names

### Example Code Style

```php
<?php

declare(strict_types=1);

namespace VerifyKit;

/**
 * Example class demonstrating code style
 */
final class Example
{
    public function __construct(
        private readonly string $property
    ) {
    }

    /**
     * Example method with documentation
     *
     * @param string $email The email to validate
     * @return bool True if valid
     */
    public function validate(string $email): bool
    {
        if (empty($email)) {
            return false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
```

## Reporting Issues

### Bug Reports

Include:
- PHP version
- SDK version
- Minimal reproduction code
- Expected vs actual behavior
- Error messages and stack traces

### Feature Requests

Include:
- Clear description of the feature
- Use cases
- Example code (if applicable)
- Why it would be beneficial

## Questions?

- Email: support@verifykit.io
- GitHub Issues: For bugs and features
- GitHub Discussions: For questions and ideas

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
