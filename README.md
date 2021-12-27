<div align="center">

![Logo](docs/logo.png)

# Cache warmup

[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=eliashaeussler_cache-warmup&metric=coverage)](https://sonarcloud.io/dashboard?id=eliashaeussler_cache-warmup)
[![Tests](https://github.com/eliashaeussler/cache-warmup/actions/workflows/tests.yaml/badge.svg)](https://github.com/eliashaeussler/cache-warmup/actions/workflows/tests.yaml)
[![CGL](https://github.com/eliashaeussler/cache-warmup/actions/workflows/cgl.yaml/badge.svg)](https://github.com/eliashaeussler/cache-warmup/actions/workflows/cgl.yaml)
[![Latest Stable Version](http://poser.pugx.org/eliashaeussler/cache-warmup/v)](https://packagist.org/packages/eliashaeussler/cache-warmup)
[![Total Downloads](http://poser.pugx.org/eliashaeussler/cache-warmup/downloads)](https://packagist.org/packages/eliashaeussler/cache-warmup)
[![Docker](https://img.shields.io/docker/v/eliashaeussler/cache-warmup?label=docker&sort=semver)](https://hub.docker.com/r/eliashaeussler/cache-warmup)
[![License](http://poser.pugx.org/eliashaeussler/cache-warmup/license)](LICENSE.md)

:package:&nbsp;[Packagist](https://packagist.org/packages/eliashaeussler/cache-warmup) |
:floppy_disk:&nbsp;[Repository](https://github.com/eliashaeussler/cache-warmup) |
:bug:&nbsp;[Issue tracker](https://github.com/eliashaeussler/cache-warmup/issues)

</div>

A PHP library to warm up caches of pages located in XML sitemaps. Cache warmup
is performed by concurrently sending a simple `HEAD` request to those pages,
either from the command-line or by using the provided PHP API. It is even
possible to write custom crawlers that take care of cache warmup.

## :rocket: Features

* Warmup caches of pages located in XML sitemaps
* Optionally warmup caches of single pages
* Console command and PHP API for cache warmup
* Additional Docker image
* Interface for custom crawler implementations

## :fire: Installation

```bash
composer require eliashaeussler/cache-warmup
```

## :zap: Usage

### Command-line usage

**General usage**

```bash
./vendor/bin/cache-warmup [--urls...] [--limit] [--progress] [--crawler] [<sitemaps>...]
```

**Extended usage**

```bash
# Warm up caches of specific sitemap
./vendor/bin/cache-warmup "https://www.example.org/sitemap.xml"

# Limit number of pages to be crawled
./vendor/bin/cache-warmup "https://www.example.org/sitemap.xml" --limit 50

# Show progress bar (can also be achieved by increasing verbosity)
./vendor/bin/cache-warmup "https://www.example.org/sitemap.xml" --progress
./vendor/bin/cache-warmup "https://www.example.org/sitemap.xml" -v

# Use custom crawler (must implement EliasHaeussler\CacheWarmup\Crawler\CrawlerInterface)
./vendor/bin/cache-warmup "https://www.example.org/sitemap.xml" --crawler "Vendor\Crawler\MyCrawler"

# Define URLs to be crawled
./vendor/bin/cache-warmup -u "https://www.example.org/" \
    -u "https://www.example.org/foo" \
    -u "https://www.example.org/baz"
```

For more detailed information run `./vendor/bin/cache-warmup --help`.

### Code usage

**General usage**

```php
// Instantiate and run cache warmer
$cacheWarmer = new \EliasHaeussler\CacheWarmup\CacheWarmer();
$cacheWarmer->addSitemaps('https://www.example.org/sitemap.xml');
$crawler = $cacheWarmer->run();

// Get successful and failed URLs
$successfulUrls = $crawler->getSuccessfulUrls();
$failedUrls = $crawler->getFailedUrls();
```

**Extended usage**

```php
// Limit number of pages to be crawled
$cacheWarmer->setLimit(50);

// Use custom crawler (must implement EliasHaeussler\CacheWarmup\Crawler\CrawlerInterface)
$crawler = new \Vendor\Crawler\MyCrawler();
$cacheWarmer->run($crawler);

// Define URLs to be crawled
$cacheWarmer->addUrl(new \GuzzleHttp\Psr7\Uri('https://www.example.org/'));
$cacheWarmer->addUrl(new \GuzzleHttp\Psr7\Uri('https://www.example.org/foo'));
$cacheWarmer->addUrl(new \GuzzleHttp\Psr7\Uri('https://www.example.org/baz'));
```

### Usage with Docker

**General usage**

```bash
docker run --rm -it eliashaeussler/cache-warmup <options>
```

**Extended usage**

```bash
# Use latest version
docker run --rm -it eliashaeussler/cache-warmup:latest <options>

# Use specific version
docker run --rm -it eliashaeussler/cache-warmup:0.3.0 <options>
```

**Usage with docker-compose**

```yaml
version: '3.6'

services:
  cache-warmup:
    image: eliashaeussler/cache-warmup
    command: [<options>]
```

## :technologist: Development

### Preparation

```bash
# Clone repository
git clone https://github.com/eliashaeussler/cache-warmup.git
cd cache-warmup

# Install Composer dependencies
composer install
```

### Run linters

```bash
# Composer normalization
composer normalize

# PHP-CS Fixer
composer lint

# PHPStan
composer sca
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

## :gem: Credits

[Background vector created by photoroyalty - www.freepik.com](https://www.freepik.com/vectors/background)

## :star: License

This project is licensed under [GNU General Public License 3.0 (or later)](LICENSE).
