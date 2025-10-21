# Contributing to Laravel ActivityLog Enricher

We love your input! We want to make contributing to this project as easy and transparent as possible, whether it's:

- Reporting a bug
- Discussing the current state of the code
- Submitting a fix
- Proposing new features
- Becoming a maintainer

## Development Process

We use GitHub to host code, to track issues and feature requests, as well as accept pull requests.

## Pull Requests

Pull requests are the best way to propose changes to the codebase. We actively welcome your pull requests:

1. Fork the repo and create your branch from `main`.
2. If you've added code that should be tested, add tests.
3. If you've changed APIs, update the documentation.
4. Ensure the test suite passes.
5. Make sure your code lints.
6. Issue that pull request!

## Development Setup

```bash
git clone https://github.com/jimmyahalpara/laravel-activitylog-enricher.git
cd laravel-activitylog-enricher
composer install
```

## Running Tests

```bash
# Run tests
composer test

# Run tests with coverage
composer test-coverage

# Run static analysis
composer analyse

# Run code formatting
composer format

# Run all quality checks
composer quality
```

## Code Style

We use PHP-CS-Fixer to maintain consistent code style. Please run `composer format` before submitting your changes.

## Static Analysis

We maintain PHPStan level 8 compliance. Please run `composer analyse` to check for any type errors.

## Reporting Bugs

We use GitHub issues to track public bugs. Report a bug by [opening a new issue](https://github.com/jimmyahalpara/laravel-activitylog-enricher/issues).

**Great Bug Reports** tend to have:

- A quick summary and/or background
- Steps to reproduce
  - Be specific!
  - Give sample code if you can
- What you expected would happen
- What actually happens
- Notes (possibly including why you think this might be happening, or stuff you tried that didn't work)

## Feature Requests

We welcome feature requests! Please [open an issue](https://github.com/jimmyahalpara/laravel-activitylog-enricher/issues) to discuss new features.

## License

By contributing, you agree that your contributions will be licensed under its MIT License.