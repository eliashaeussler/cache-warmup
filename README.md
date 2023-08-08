<div align="center">

[![Screenshot](docs/screenshot.png)](#-installation)

# Cache warmup

[![Coverage](https://img.shields.io/codecov/c/github/eliashaeussler/cache-warmup?logo=codecov&token=SAYQJPAHYS)](https://codecov.io/gh/eliashaeussler/cache-warmup)
[![Maintainability](https://img.shields.io/codeclimate/maintainability/eliashaeussler/cache-warmup?logo=codeclimate)](https://codeclimate.com/github/eliashaeussler/cache-warmup/maintainability)
[![CGL](https://img.shields.io/github/actions/workflow/status/eliashaeussler/cache-warmup/cgl.yaml?label=cgl&logo=github)](https://github.com/eliashaeussler/cache-warmup/actions/workflows/cgl.yaml)
[![Tests](https://img.shields.io/github/actions/workflow/status/eliashaeussler/cache-warmup/tests.yaml?label=tests&logo=github)](https://github.com/eliashaeussler/cache-warmup/actions/workflows/tests.yaml)
[![Supported PHP Versions](https://img.shields.io/packagist/dependency-v/eliashaeussler/cache-warmup/php?logo=php)](https://packagist.org/packages/eliashaeussler/cache-warmup)

</div>

A PHP library to warm up caches of URLs located in XML sitemaps. Cache warmup
is performed by concurrently sending simple `HEAD` requests to those URLs,
either from the command-line or by using the provided PHP API. The whole warmup
process is highly customizable, e.g. by defining a crawling limit, excluding
sitemaps and URLs by exclusion patterns or by using a specific crawling strategy.
It is even possible to write custom crawlers that take care of cache warmup.

## 🚀 Features

* Warm up caches of URLs located in XML sitemaps
* Console command and PHP API for cache warmup
* Support for sitemap indexes
* Exclusion patterns for sitemaps and URLs
* Various crawling strategies
* Support for gzipped XML sitemaps
* Interface for custom crawler implementations

## 🔥 Installation

### PHAR (recommended)

[![PHAR](https://img.shields.io/github/v/release/eliashaeussler/cache-warmup?label=version&logo=github&sort=semver)](https://github.com/eliashaeussler/cache-warmup/releases)
[![PHAR](https://img.shields.io/github/downloads/eliashaeussler/cache-warmup/total?color=brightgreen)](https://github.com/eliashaeussler/cache-warmup/releases)

Head over to the [latest GitHub release][20] and download the [`cache-warmup.phar`][1] file.

Run `chmod +x cache-warmup.phar` to make it executable.

### PHIVE

[![PHIVE](https://img.shields.io/github/v/release/eliashaeussler/cache-warmup?label=version&logo=php&sort=semver)](https://phar.io)

```bash
phive install cache-warmup
```

### Docker

[![Docker](https://img.shields.io/docker/v/eliashaeussler/cache-warmup?label=version&logo=docker&sort=semver)](https://hub.docker.com/r/eliashaeussler/cache-warmup)
[![Docker Pulls](https://img.shields.io/docker/pulls/eliashaeussler/cache-warmup?color=brightgreen)](https://hub.docker.com/r/eliashaeussler/cache-warmup)

```bash
docker run --rm -it eliashaeussler/cache-warmup
```

You can also use the image from [GitHub Container Registry][21]:

```bash
docker run --rm -it ghcr.io/eliashaeussler/cache-warmup
```

### Composer

[![Packagist](https://img.shields.io/packagist/v/eliashaeussler/cache-warmup?label=version&logo=packagist)](https://packagist.org/packages/eliashaeussler/cache-warmup)
[![Packagist Downloads](https://img.shields.io/packagist/dt/eliashaeussler/cache-warmup?color=brightgreen)](https://packagist.org/packages/eliashaeussler/cache-warmup)

```bash
composer require eliashaeussler/cache-warmup
```

## ⚡ Usage

### Command-line usage

```bash
$ cache-warmup [options] [<sitemaps>...]
```

The following input parameters are available:

#### `sitemaps`

URLs of XML sitemaps to be warmed up.

```bash
$ cache-warmup "https://www.example.org/sitemap.xml" "https://www.example.org/de/sitemap.xml"
```

| Shorthand               | –                                                                            |
|:------------------------|:-----------------------------------------------------------------------------|
| Required                | ✅ *(either [`sitemaps`](#sitemaps) or [`--urls`](#--urls) must be provided)* |
| Multiple values allowed | ✅                                                                            |
| Default                 | **–**                                                                        |

#### `--urls`

Additional URLs to be warmed up.

```bash
$ cache-warmup -u "https://www.example.org/" -u "https://www.example.org/de/"
```

| Shorthand               | `-u`                                                                         |
|:------------------------|:-----------------------------------------------------------------------------|
| Required                | ✅ *(either [`sitemaps`](#sitemaps) or [`--urls`](#--urls) must be provided)* |
| Multiple values allowed | ✅                                                                            |
| Default                 | **–**                                                                        |

#### `--exclude`

Patterns of URLs to be excluded from cache warmup.

The following patterns can be configured:

* Regular expressions with delimiter `#`, e.g. `#(no_cache|no_warming)=1#`
* Patterns for use with [`fnmatch`][2], e.g. `*no_cache=1*`

```bash
$ cache-warmup -e "#(no_cache|no_warming)=1#" -e "*no_cache=1*"
```

| Shorthand               | `-e`  |
|:------------------------|:------|
| Required                | **–** |
| Multiple values allowed | ✅     |
| Default                 | **–** |

#### `--limit`

Limit the number of URLs to be processed.

```bash
$ cache-warmup --limit 250
```

| Shorthand               | `-l`                 |
|:------------------------|:---------------------|
| Required                | **–**                |
| Multiple values allowed | **–**                |
| Default                 | **0** *(= no limit)* |

#### `--progress`

Show a progress bar during cache warmup.

> 💡 You can show a more verbose progress bar by increasing output verbosity
> with the `--verbose` command option.

```bash
$ cache-warmup --progress
```

| Shorthand               | `-p`   |
|:------------------------|:-------|
| Required                | **–**  |
| Multiple values allowed | **–**  |
| Default                 | **no** |

#### `--crawler`

FQCN of the crawler to use for cache warmup.

The crawler must implement one the following interfaces:

* [`EliasHaeussler\CacheWarmup\Crawler\CrawlerInterface`][3] represents a basic
  crawler and must always be implemented (the following interfaces extend this
  interface).
* [`EliasHaeussler\CacheWarmup\Crawler\VerboseCrawlerInterface`][4] receives the
  current console output to generate user-oriented output.
* [`EliasHaeussler\CacheWarmup\Crawler\ConfigurableCrawlerInterface`][5] allows to
  make crawlers configurable (see [`--crawler-options`](#--crawler-options)).

```bash
$ cache-warmup --crawler "Vendor\Crawler\MyCustomCrawler"
```

| Shorthand               | `-c`               |
|:------------------------|:-------------------|
| Required                | **–**              |
| Multiple values allowed | **–**              |
| Default                 | **–**<sup>1)</sup> |

*<sup>1)</sup> The default crawler depends on whether the command option
[`--progress`](#--progress) is set. In this case the [`OutputtingCrawler`][6]
is used, otherwise the [`ConcurrentCrawler`][7].*

#### `--crawler-options`

A JSON-encoded string of additional config for configurable crawlers.

> ⚠️ These options only apply to crawlers implementing
> [`EliasHaeussler\CacheWarmup\Crawler\ConfigurableCrawlerInterface`][5]. If the
> configured crawler does not implement this interface, a warning is shown in case
> crawler options are configured.

```bash
$ cache-warmup --crawler-options '{"concurrency": 3, "request_options": {"delay": 3000}}'
```

| Shorthand               | `-o`       |
|:------------------------|:-----------|
| Required                | **–**      |
| Multiple values allowed | **–**      |
| Default                 | **`null`** |

#### `--strategy`

Optional crawling strategy to prepare URLs before crawling them.

The following strategies are currently available:

* [`sort-by-changefreq`][8]
* [`sort-by-lastmod`][9]
* [`sort-by-priority`][10]

```bash
$ cache-warmup --strategy sort-by-priority
```

| Shorthand               | `-s`                            |
|:------------------------|:--------------------------------|
| Required                | **–**                           |
| Multiple values allowed | **–**                           |
| Default                 | **–** *(= crawl by occurrence)* |

#### `--format`

The formatter used to print the cache warmup result.

At the moment, the following formatters are available:

* [`json`][11]
* [`text`][12] *(default)*

```bash
$ cache-warmup --format json
```

| Shorthand               | `-f`       |
|:------------------------|:-----------|
| Required                | ✅          |
| Multiple values allowed | **–**      |
| Default                 | **`text`** |

#### `--allow-failures`

Allow failures during URL crawling and exit with zero.

```bash
$ cache-warmup --allow-failures
```

| Shorthand               | –      |
|:------------------------|:-------|
| Required                | **–**  |
| Multiple values allowed | **–**  |
| Default                 | **no** |

#### `--repeat-after`

Run cache warmup in endless loop and repeat x seconds after each run.

```bash
$ cache-warmup --repeat-after 300
```

> ⚠️ If cache warmup fails, the command fails immediately and is not repeated.
> To continue in case of failures, the [`--allow-failures`](#--allow-failures)
> command option must be passed as well.

| Shorthand               | –                           |
|:------------------------|:----------------------------|
| Required                | **–**                       |
| Multiple values allowed | **–**                       |
| Default                 | **`0`** *(= run only once)* |

💡 Run `cache-warmup --help` to see a detailed explanation of all available
input parameters.

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

## 📂 Configuration

### Crawler configuration

Both default crawlers are implemented as configurable crawlers:

* [`EliasHaeussler\CacheWarmup\Crawler\ConcurrentCrawler`][7]
* [`EliasHaeussler\CacheWarmup\Crawler\OutputtingCrawler`][6]

The following configuration can be passed either on command-line
as JSON-encoded string (see [`--crawler-options`](#--crawler-options)
command option) or as associative array in the constructor when
using the library with PHP.

#### `concurrency`

Define how many URLs are crawled concurrently.

> 💡 Internally, Guzzle's [Pool][13] feature is used to send multiple requests
> concurrently using asynchronous requests. You may also have a look at how
> this is implemented in the library's [`RequestPoolFactory`][14].

```php
$crawler = new \EliasHaeussler\CacheWarmup\Crawler\ConcurrentCrawler([
    'concurrency' => 3,
]);
```

| Type    | `integer` |
|:--------|:----------|
| Default | **`5`**   |

#### `request_method`

The [HTTP method][15] used to perform cache warmup requests.

```php
$crawler = new \EliasHaeussler\CacheWarmup\Crawler\ConcurrentCrawler([
    'request_method' => 'GET',
]);
```

| Type    | `string`   |
|:--------|:-----------|
| Default | **`HEAD`** |

#### `request_headers`

A list of [HTTP headers][16] to send with each cache warmup request.

```php
$crawler = new \EliasHaeussler\CacheWarmup\Crawler\ConcurrentCrawler([
    'request_headers' => [
        'X-Foo' => 'bar',
        'User-Agent' => 'Foo-Crawler/1.0',
    ],
]);
```

| Type      | `array<string, string>`                                     |
|:----------|:------------------------------------------------------------|
| Mergeable | ✅                                                           |
| Default   | **`['User-Agent' => '<default user-agent>']`**<sup>2)</sup> |

*<sup>2)</sup>The default user-agent is built in [`ConcurrentCrawlerTrait::getRequestHeaders()`][17].*

#### `request_options`

Additional [request options][18] used for each cache warmup request.

```php
$crawler = new \EliasHaeussler\CacheWarmup\Crawler\ConcurrentCrawler([
    'request_options' => [
        'delay' => 500,
        'timeout' => 10,
    ],
]);
```

| Type      | `array<string, mixed>` |
|:----------|:-----------------------|
| Mergeable | **–**                  |
| Default   | **`[]`**               |

#### `client_config`

Optional [configuration][19] used when instantiating a new Guzzle client.

> ⚠️ Client configuration is ignored when running cache warmup from the
> command-line. In addition, it is only respected if a new client is
> instantiated *within* the crawler. If an existing client is passed to
> the crawler, client configuration is ignored.

```php
$stack = \GuzzleHttp\HandlerStack::create();
$stack->push($customMiddleware);

$crawlerConfig = [
    'client_config' => [
        'handler' => $stack,
    ],
];

// ✅ This works as expected
$crawler = new \EliasHaeussler\CacheWarmup\Crawler\ConcurrentCrawler(
    $crawlerConfig,
);

// ❌ This does *not* respect client configuration
$crawler = new \EliasHaeussler\CacheWarmup\Crawler\ConcurrentCrawler(
    $crawlerConfig,
    new \GuzzleHttp\Client(),
);
```

| Type      | `array<string, mixed>` |
|:----------|:-----------------------|
| Mergeable | **–**                  |
| Default   | **`[]`**               |

## 🧑‍💻 Contributing

Please have a look at [`CONTRIBUTING.md`](CONTRIBUTING.md).

## ⭐ License

This project is licensed under [GNU General Public License 3.0 (or later)](LICENSE).

[1]: https://github.com/eliashaeussler/cache-warmup/releases/latest/download/cache-warmup.phar
[2]: https://www.php.net/manual/de/function.fnmatch.php
[3]: src/Crawler/CrawlerInterface.php
[4]: src/Crawler/VerboseCrawlerInterface.php
[5]: src/Crawler/ConfigurableCrawlerInterface.php
[6]: src/Crawler/OutputtingCrawler.php
[7]: src/Crawler/ConcurrentCrawler.php
[8]: src/Crawler/Strategy/SortByChangeFrequencyStrategy.php
[9]: src/Crawler/Strategy/SortByLastModificationDateStrategy.php
[10]: src/Crawler/Strategy/SortByPriorityStrategy.php
[11]: src/Formatter/JsonFormatter.php
[12]: src/Formatter/TextFormatter.php
[13]: https://docs.guzzlephp.org/en/stable/quickstart.html#concurrent-requests
[14]: src/Http/Message/RequestPoolFactory.php
[15]: https://docs.guzzlephp.org/en/stable/psr7.html#request-methods
[16]: https://docs.guzzlephp.org/en/stable/request-options.html#headers
[17]: src/Crawler/ConcurrentCrawlerTrait.php
[18]: https://docs.guzzlephp.org/en/stable/request-options.html
[19]: https://docs.guzzlephp.org/en/stable/quickstart.html#creating-a-client
[20]: https://github.com/eliashaeussler/cache-warmup/releases/latest
[21]: https://github.com/eliashaeussler/cache-warmup/pkgs/container/cache-warmup
