# Contribution guide

Thanks for considering contributing to this project. Contributions of any size are highly appreciated.

To keep the code base consistent and maintainable, please follow the workflow described below before
submitting a pull request.

## Requirements

- PHP >= 8.2
- Composer
- Docker (for E2E tests)
- GPG (for E2E tests)
- [`jq`](https://github.com/jqlang/jq) (for E2E tests)

## Preparation

```bash
# Clone repository
git clone https://github.com/eliashaeussler/cache-warmup.git
cd cache-warmup

# Install dependencies
composer install
```

## Development workflow

A typical contribution workflow looks like this:

1. Apply automatic fixes.
2. Run all checks.
3. Run the test suite.
4. Submit a pull request.

### Apply automatic fixes

Use the following commands to normalize and format the code base:

```bash
# Apply all automatic fixes
composer fix

# Apply specific fixes
composer fix:composer
composer fix:editorconfig
composer fix:php
```

### Run checks

Use `composer check` to run the full code quality pipeline locally. This command bundles dependency analysis,
static analysis, coding style checks, and Rector in dry-run mode so that potential refactorings can be reviewed
without changing files.

```bash
# Run all checks
composer check

# Run specific checks
composer check:deps
composer check:refactor
composer check:static
composer check:style

# Run specific style checks
composer check:style:composer
composer check:style:editorconfig
composer check:style:php
```

### Run refactorings

Refactorings are intentionally separated from regular checks because they may change the code base.

```bash
# Run all configured refactorings
composer refactor

# Run specific refactorings
composer refactor:php
```

### Run tests

Run the full test suite before opening a pull request:

```bash
# Run all tests
composer test

# Run E2E tests
composer test:e2e
composer test:e2e:docker
composer test:e2e:phar

# Run unit tests
composer test:unit

# Run unit tests with code coverage
composer test:unit:coverage
```

## Coverage reports

Code coverage reports are written to `build/tests/coverage`. Open the latest HTML report with:

```bash
open build/tests/coverage/html/index.html
```

## Pull requests

Once the changes are ready, please [submit a pull request](https://github.com/eliashaeussler/cache-warmup/compare)
and describe what was changed and why. Ideally, the pull request references an issue that describes the
problem being solved.

All documented code quality tools are executed automatically for pull requests across the currently
supported PHP versions. For details, refer to the [GitHub Actions workflows](../.github/workflows).

## Benchmarks (maintainers only)

First, make sure you have [DDEV](https://ddev.com/) installed. Now run benchmarks:

```bash
ddev start
composer test:benchmarks
```
