# Frequently Asked Questions (FAQ)

## Are sitemap indexes supported?

Yes, the XML parser detects if an XML sitemap or a sitemap index is parsed.
Referenced XML sitemaps in sitemap indexes are followed until a potentially
configured [limit](config-reference/limit.md) is reached.

## I can't see any valuable output during cache warmup. How can I debug the process?

There exist various debugging and logging tools to increase verbosity of the
cache warmup process. Take a look at the [`logFile`](config-reference/log-file.md),
[`logLevel`](config-reference/log-level.md) and [`progress`](config-reference/progress.md)
configuration options. You may also increase output verbosity by using the `-v`
command option.

## Can I limit the number of concurrently warmed URLs?

When using the default crawlers, you can configure the concurrency value using
the [`concurrency`](config-reference/crawler-options.md#concurrency) crawler option.

## Is it possible to crawl URLs with `GET` instead of `HEAD`?

Yes, this can be configured by using the [`request_method`](config-reference/crawler-options.md#request-method)
crawler option in combination with one of the default crawlers.

## How can I configure basic auth credentials?

This is possible by using the [`request_options`](config-reference/crawler-options.md#request-options)
crawler option in combination with one of the default crawlers. In addition,
the [`client_config`](config-reference/parser-options.md#client-config) parser
option in combination with the default parser can be used. Both crawler option
and parser option accept all configurable Guzzle request options such as
[`auth`](https://docs.guzzlephp.org/en/stable/request-options.html#auth) for
basic auth.

Example:

::: code-group

```bash [CLI]
./cache-warmup.phar \
    --crawler-options '{"request_options": {"auth": ["username", "password"]}}' \
    --parser-options '{"client_config": {"auth": ["username", "password"]}}'
```

```json [JSON]
{
    "crawlerOptions": {
        "request_options": {
            "auth": ["username", "password"]
        }
    },
    "parserOptions": {
        "client_config": {
            "auth": ["username", "password"]
        }
    }
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $basicAuth = ['username', 'password'];

    $config->setCrawlerOption('request_options', [
        'auth' => $basicAuth,
    ]);
    $config->setParserOption('client_config', [
        'auth' => $basicAuth,
    ]);

    return $config;
};
```

```yaml [YAML]
crawlerOptions:
  request_options:
    auth: ['username', 'password']
parserOptions:
  client_config:
    auth: ['username', 'password']
```

```bash [.env]
CACHE_WARMUP_CRAWLER_OPTIONS='{"request_options": {"auth": ["username", "password"]}}'
CACHE_WARMUP_PARSER_OPTIONS='{"client_config": {"auth": ["username", "password"]}}'
```

:::

## Can I use a custom `User-Agent` header instead of the default one?

Yes, a custom `User-Agent` header can be configured by using the
[`request_headers`](config-reference/crawler-options.md#request-headers) crawler
option in combination with one of the default crawlers. In addition,
it can be configured by using the
[`request_headers`](config-reference/parser-options.md#request-headers) parser
option in combination with the default parser.

## What does "default crawlers" actually mean?

The library ships with two default crawlers. Depending on the provided configuration
options, one of the crawlers is used for cache warmup, unless you configure a custom
crawler by using the [`crawler`](config-reference/crawler.md) configuration option.
Read more at [Default crawlers](api/index.md#default-crawlers).
