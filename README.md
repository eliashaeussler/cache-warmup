<div align="center">

![Logo](docs/logo.png)

# Cache warmup

[![Coverage](https://codecov.io/gh/eliashaeussler/cache-warmup/branch/main/graph/badge.svg?token=SAYQJPAHYS)](https://codecov.io/gh/eliashaeussler/cache-warmup)
[![Maintainability](https://api.codeclimate.com/v1/badges/20217c57aa1fc511f8bc/maintainability)](https://codeclimate.com/github/eliashaeussler/cache-warmup/maintainability)
[![Tests](https://github.com/eliashaeussler/cache-warmup/actions/workflows/tests.yaml/badge.svg)](https://github.com/eliashaeussler/cache-warmup/actions/workflows/tests.yaml)
[![CGL](https://github.com/eliashaeussler/cache-warmup/actions/workflows/cgl.yaml/badge.svg)](https://github.com/eliashaeussler/cache-warmup/actions/workflows/cgl.yaml)
[![Release](https://github.com/eliashaeussler/cache-warmup/actions/workflows/release.yaml/badge.svg)](https://github.com/eliashaeussler/cache-warmup/actions/workflows/release.yaml)
[![Latest Stable Version](http://poser.pugx.org/eliashaeussler/cache-warmup/v)](https://packagist.org/packages/eliashaeussler/cache-warmup)
[![PHP Version Require](http://poser.pugx.org/eliashaeussler/cache-warmup/require/php)](https://packagist.org/packages/eliashaeussler/cache-warmup)
[![Total Downloads](http://poser.pugx.org/eliashaeussler/cache-warmup/downloads)](https://packagist.org/packages/eliashaeussler/cache-warmup)
[![Docker](https://img.shields.io/docker/v/eliashaeussler/cache-warmup?label=docker&sort=semver)](https://hub.docker.com/r/eliashaeussler/cache-warmup)
[![License](http://poser.pugx.org/eliashaeussler/cache-warmup/license)](LICENSE)

📦&nbsp;[Packagist](https://packagist.org/packages/eliashaeussler/cache-warmup) |
💾&nbsp;[Repository](https://github.com/eliashaeussler/cache-warmup) |
🐛&nbsp;[Issue tracker](https://github.com/eliashaeussler/cache-warmup/issues)

</div>

A PHP library to warm up caches of pages located in XML sitemaps. Cache warmup
is performed by concurrently sending a simple `HEAD` request to those pages,
either from the command-line or by using the provided PHP API. It is even
possible to write custom crawlers that take care of cache warmup.

## 🚀 Features

* Warmup caches of pages located in XML sitemaps
* Optionally warmup caches of single pages
* Console command and PHP API for cache warmup
* Additional Docker image
* Interface for custom crawler implementations

## 🔥 Installation

### Composer

```bash
composer require eliashaeussler/cache-warmup
```

### Phar

Head over to <https://github.com/eliashaeussler/cache-warmup/releases/latest> and
download the latest [`cache-warmup.phar`](https://github.com/eliashaeussler/cache-warmup/releases/latest/download/cache-warmup.phar) file.

Run `chmod +x cache-warmup.phar` to make it executable.

### PHIVE

```bash
phive install eliashaeussler/cache-warmup
```

### Docker

Please have a look at [`Usage with Docker`](#usage-with-docker).

## ⚡ Usage

### Command-line usage

```bash
vendor/bin/cache-warmup [options] [<sitemaps>...]
```

The following input parameters are available:

| Parameter                 | Description                                                                                                                                             |
|---------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------|
| `sitemaps`                | URLs of XML sitemaps to be warmed up *(multiple values allowed)*                                                                                        |
| `--urls`, `-u`            | Additional URLs to be warmed up *(multiple values allowed)*                                                                                             |
| `--limit`, `-l`           | Limit the number of URLs to be processed *(default: 0)*                                                                                                 |
| `--progress`, `-p`        | Show progress bar during cache warmup                                                                                                                   |
| `--crawler`, `-c`         | FQCN of the crawler to use for cache warming (must implement [`EliasHaeussler\CacheWarmup\Crawler\CrawlerInterface`](src/Crawler/CrawlerInterface.php)) |
| `--crawler-options`, `-o` | JSON-encoded string of additional config for configurable crawlers                                                                                      |
| `--allow-failures`        | Allow failures during URL crawling and exit with zero                                                                                                   |
| `--format`, `-f`          | Formatter used to print the cache warmup result, can be `json` or `text` *(default: `text`)*                                                            |

💡 Run `vendor/bin/cache-warmup --help` to see a detailed explanation of
all available input parameters.

### Code usage

```php
// Instantiate and run cache warmer
$cacheWarmer = new \EliasHaeussler\CacheWarmup\CacheWarmer();
$cacheWarmer->addSitemaps('https://www.example.org/sitemap.xml');
$result = $cacheWarmer->run();

// Get successful and failed URLs
$successfulUrls = $result->getSuccessful();
$failedUrls = $result->getFailed();
```

### Usage with Docker

```bash
docker run --rm -it eliashaeussler/cache-warmup [options] [<sitemaps>...]
```

## 🧑‍💻 Contributing

Please have a look at [`CONTRIBUTING.md`](CONTRIBUTING.md).

## 💎 Credits

[Background vector created by photoroyalty - www.freepik.com](https://www.freepik.com/vectors/background)

## ⭐ License

This project is licensed under [GNU General Public License 3.0 (or later)](LICENSE).
