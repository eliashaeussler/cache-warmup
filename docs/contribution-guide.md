# Contribution guide

Thanks for considering contributing to this project! Each contribution is
highly appreciated. In order to maintain a high code quality, please follow
all steps below.

## Requirements

- PHP >= 8.1
- Composer >= 2.1

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

# All tests with code coverage
composer test:coverage
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
[workflows](https://github.com/eliashaeussler/cache-warmup/tree/main/.github/workflows)
to get a detailed overview.
