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

## Usage

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
