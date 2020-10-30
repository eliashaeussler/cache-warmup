[![Pipeline](https://gitlab.elias-haeussler.de/eliashaeussler/cache-warmup/badges/master/pipeline.svg)](https://gitlab.elias-haeussler.de/eliashaeussler/cache-warmup/-/pipelines)
[![Coverage](https://gitlab.elias-haeussler.de/eliashaeussler/cache-warmup/badges/master/coverage.svg)](https://gitlab.elias-haeussler.de/eliashaeussler/cache-warmup/-/pipelines)
[![Packagist](https://badgen.net/packagist/v/eliashaeussler/cache-warmup)](https://packagist.org/packages/eliashaeussler/cache-warmup)
[![License](https://badgen.net/packagist/license/eliashaeussler/cache-warmup)](LICENSE)

# Cache warmup

> Composer package to warm up caches of pages located in XML sitemaps

## Installation

```bash
composer req --dev eliashaeussler/cache-warmup
```

## Development

### Preparation

```bash
# Clone repository
git clone https://gitlab.elias-haeussler.de/eliashaeussler/cache-warmup.git
cd cache-warmup

# Install Composer dependencies
composer install
```

### Run tests

Unit tests of this plugin can be executed using the provided Composer
script `test`. You can pass all available arguments to PHPUnit.

```bash
# Run tests
composer test

# Run tests and print coverage result
composer test -- --coverage-text
```

## License

[GPL 3.0 or later](LICENSE)
