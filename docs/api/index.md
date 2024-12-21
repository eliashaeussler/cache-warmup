# API Reference

The library provides a PHP API for use in other systems and
applications, e.g. within a content management system.

::: info
Only a limited set of configuration options is available within
the PHP API. For a greater experience, we suggest to
[use the library from command line](../installation.md) along with
the whole set of available [configuration options](../config-reference/index.md).
:::

## `CacheWarmer`

The [`CacheWarmer`](../../src/CacheWarmer.php) class serves as main
entrypoint for the PHP API.

```php
use EliasHaeussler\CacheWarmup;

// Instantiate and run cache warmer
$cacheWarmer = new CacheWarmup\CacheWarmer();
$cacheWarmer->addSitemaps('https://www.example.org/sitemap.xml');
$result = $cacheWarmer->run();

// Get successful and failed URLs
$successfulUrls = $result->getSuccessful();
$failedUrls = $result->getFailed();
```

Check out all available [options](options.md) and
[methods](methods.md) to get an overview about possible API
opportunities.

## `Crawler`

URLs in XML sitemaps are processed by crawlers implementing
[`Crawler\Crawler`](../../src/Crawler/Crawler.php). Read more
about how to [create a custom crawler](crawler.md).

In addition, there exist different variations of crawler
implementations:

::: info ⚙️ [Configurable Crawler](configurable-crawler.md)
Allows to customize crawling behavior using crawler options.
:::

::: info 📝 [Logging Crawler](logging-crawler.md)
Provides logging features for cache warmup requests.
:::

::: info 🧯 [Stoppable Crawler](stoppable-crawler.md)
Makes crawlers stop further processing in case of a failure.
:::

::: info 🗣️ [Verbose Crawler](verbose-crawler.md)
Enhances the cache warmup process with user-oriented output.
:::

### Default crawlers

The library ships with two default crawlers:

* [`Crawler\ConcurrentCrawler`](../../src/Crawler/ConcurrentCrawler.php)
* [`Crawler\OutputtingCrawler`](../../src/Crawler/OutputtingCrawler.php)

You can find all available crawler options in the
[`crawlerOptions`](../config-reference/crawler-options.md#option-reference)
configuration reference.

## `Parser`

XML sitemaps are fetched and parsed by parsers implementing
[`Xml\Parser`](../../src/Xml/Parser.php). Read more about how
to [create a custom parser](parser.md).

In addition, there exist different variations of parser implementations:

::: info ⚙️ [Configurable Parser](configurable-parser.md)
Allows to customize parsing behavior using parser options.
:::

### Default parser

The library ships with a default parser:

* [`Xml\SitemapXmlParser`](../../src/Xml/SitemapXmlParser.php)

You can find all available parser options in the
[`parserOptions`](../config-reference/parser-options.md#option-reference)
configuration reference.
