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

This is possible by using the [`clientOptions`](config-reference/client-options.md)
configuration option in combination with one of the default crawlers and the
default parser. Pass your basic auth credentials with the
[`auth`](https://docs.guzzlephp.org/en/stable/request-options.html#auth) request
option, for example:

::: code-group

```bash [CLI]
./cache-warmup.phar --client-options '{"auth": ["username", "password"]}'
```

```json [JSON]
{
    "clientOptions": {
        "auth": ["username", "password"]
    }
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;
use GuzzleHttp\RequestOptions;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->setClientOption(RequestOptions::AUTH, ['username', 'password']);

    return $config;
};
```

```yaml [YAML]
clientOptions:
  auth: ['username', 'password']
```

```bash [.env]
CACHE_WARMUP_CLIENT_OPTIONS='{"auth": ["username", "password"]}'
```

:::

## Can I use a custom `User-Agent` header instead of the default one?

Yes, a custom `User-Agent` header can be configured by using the
[`request_headers`](config-reference/crawler-options.md#request-headers) crawler
option in combination with one of the default crawlers. In addition,
it can be configured by using the
[`request_headers`](config-reference/parser-options.md#request-headers) parser
option in combination with the default parser.

## How can I reduce memory consumption and CPU load?

When crawling large sitemaps, memory consumption and CPU load may increase rapidly.
The following measures can reduce consumption and save resources:

* Avoid [`progress`](config-reference/progress.md) together with `-v`/`--verbose`
  option. At best, do not use `--progress`.
* Make sure the crawler option
  [`write_response_body`](config-reference/crawler-options.md#write-response-body)
  is set to `false` (default).

## What does "default crawlers" actually mean?

The library ships with two crawlers. Depending on the provided configuration options,
one of the crawlers is used for cache warmup, unless you configure a custom crawler
by using the [`crawler`](config-reference/crawler.md) configuration option. Read more
at [Default crawlers](api/index.md#default-crawlers).
