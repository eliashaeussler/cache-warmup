# Contribution guide

Thanks for considering contributing to this project! Each contribution is
highly appreciated. In order to maintain a high code quality, please follow
all steps below.

## Requirements

- PHP >= 8.2
- Docker (when running E2E tests)
- GPG (when running E2E tests)
- [`jq`](https://github.com/jqlang/jq) (when running E2E tests)

## Preparation

```bash
# Clone repository
git clone https://github.com/eliashaeussler/cache-warmup.git
cd cache-warmup

# Install dependencies
composer install
```

## Run linters

```bash
# All linters
composer lint

# Specific linters
composer lint:composer
composer lint:editorconfig
composer lint:php

# Fix all CGL issues
composer fix

# Fix specific CGL issues
composer fix:composer
composer fix:editorconfig
composer fix:php
```

## Run static code analysis

```bash
# All static code analyzers
composer sca

# Specific static code analyzers
composer sca:php
```

## Run tests

```bash
# All tests
composer test

# E2E tests
composer test:e2e
composer test:e2e:docker
composer test:e2e:phar

# Unit tests
composer test:unit

# Unit tests with code coverage
composer test:unit:coverage
```

### Test reports

Code coverage reports are written to `.build/coverage`. You can open the
last HTML report like follows:

```bash
open .build/coverage/html/index.html
```

## Submit a pull request

Once you have finished your work, please
[**submit a pull request**](https://github.com/eliashaeussler/cache-warmup/compare)
and describe what you've done. Ideally, your PR references an issue describing
the problem you're trying to solve.

All described code quality tools are automatically executed on each pull request
for all currently supported PHP versions. Take a look at the appropriate
[workflows](../.github/workflows)
to get a detailed overview.
