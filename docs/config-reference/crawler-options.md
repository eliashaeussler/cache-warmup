---
outline: [2,3]
---

# Crawler options <Badge type="tip" text="0.7.13+" />

<small>📝 Name: `crawlerOptions` &middot; 🖥️ Option: `--crawler-options`</small>

> Additional options for configurable crawlers.

::: info
These options only apply to crawlers implementing
[`EliasHaeussler\CacheWarmup\Crawler\ConfigurableCrawlerInterface`](https://github.com/eliashaeussler/cache-warmup/blob/main/src/Crawler/ConfigurableCrawlerInterface.php).
If the configured crawler does not implement this interface, a warning is
shown in case crawler options are configured.
:::

## Example

Pass crawler options in the expected input format.

::: warning
When passing crawler options as **command parameter** or **environment variable**,
make sure to pass them as **JSON-encoded string**.
:::

::: code-group

```bash [CLI]
./cache-warmup.phar --crawler-options '{"concurrency": 3, "request_options": {"delay": 3000}}'
```

```json [JSON]
{
    "crawlerOptions": {
        "concurrency": 3,
        "request_options": {
            "delay": 3000
        }
    }
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->setCrawlerOption('concurrency', 3);
    $config->setCrawlerOption('request_options', [
        'delay' => 3000,
    ]);

    return $config;
};
```

```yaml [YAML]
crawlerOptions:
  concurrency: 3
  request_options:
    delay: 3000
```

```bash [.env]
CACHE_WARMUP_CRAWLER_OPTIONS='{"concurrency": 3, "request_options": {"delay": 3000}}'
```

:::

## Option Reference

Both default crawlers are implemented as configurable crawlers:

* [`EliasHaeussler\CacheWarmup\Crawler\ConcurrentCrawler`](https://github.com/eliashaeussler/cache-warmup/blob/main/src/Crawler/ConcurrentCrawler.php)
* [`EliasHaeussler\CacheWarmup\Crawler\OutputtingCrawler`](https://github.com/eliashaeussler/cache-warmup/blob/main/src/Crawler/OutputtingCrawler.php)

The following configuration options are currently available for both crawlers:

### `client_config`

<small>🎨 Type: `array<string, mixed>` &middot; 🐝 Default: `[]`</small>

> Optional [configuration](https://docs.guzzlephp.org/en/stable/quickstart.html#creating-a-client)
> used when instantiating a new Guzzle client.

::: info
This crawler option can only be configured with a PHP configuration file.
:::

::: code-group

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $stack = \GuzzleHttp\HandlerStack::create();
    $stack->push($customMiddleware);

    $config->setCrawlerOption('client_config', [
        'handler' => $stack,
    ]);

    return $config;
};
```

:::

### `concurrency`

<small>🎨 Type: `integer` &middot; 🐝 Default: `3`</small>

> Define how many URLs are crawled concurrently.

::: info
Internally, Guzzle's [Pool](https://docs.guzzlephp.org/en/stable/quickstart.html#concurrent-requests)
feature is used to send multiple requests  concurrently using asynchronous
requests. You may also have a look at how  this is implemented in the library's
[`RequestPoolFactory`](https://github.com/eliashaeussler/cache-warmup/blob/main/src/Http/Message/RequestPoolFactory.php).
:::

::: code-group

```bash [CLI]
./cache-warmup.phar --crawler-options '{"concurrency": 5}'
```

```json [JSON]
{
    "crawlerOptions": {
        "concurrency": 5
    }
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->setCrawlerOption('concurrency', 5);

    return $config;
};
```

```yaml [YAML]
crawlerOptions:
  concurrency: 5
```

```bash [.env]
CACHE_WARMUP_CRAWLER_OPTIONS='{"concurrency": 5}'
```

:::

### `request_headers`

<small>🎨 Type: `array<string, mixed>` &middot; 🐝 Default: `['User-Agent' => '<default user-agent>']`</small>

> A list of [HTTP headers](https://docs.guzzlephp.org/en/stable/request-options.html#headers)
> to send with each cache warmup request.

::: info
The default User-Agent is built in
[`ConcurrentCrawlerTrait::getRequestHeaders()`](https://github.com/eliashaeussler/cache-warmup/blob/main/src/Crawler/ConcurrentCrawlerTrait.php).
:::

::: code-group

```bash [CLI]
./cache-warmup.phar --crawler-options '{"request_headers": {"X-Foo": "bar", "User-Agent": "Foo-Crawler/1.0"}}'
```

```json [JSON]
{
    "crawlerOptions": {
        "request_headers": {
            "X-Foo": "bar",
            "User-Agent": "Foo-Crawler/1.0"
        }
    }
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->setCrawlerOption('request_headers', [
        'X-Foo' => 'bar',
        'User-Agent' => 'Foo-Crawler/1.0',
    ]);

    return $config;
};
```

```yaml [YAML]
crawlerOptions:
  request_headers:
    X-Foo: bar
    User-Agent: 'Foo-Crawler/1.0'
```

```bash [.env]
CACHE_WARMUP_CRAWLER_OPTIONS='{"request_headers": {"X-Foo": "bar", "User-Agent": "Foo-Crawler/1.0"}}'
```

:::

### `request_method`

<small>🎨 Type: `string` &middot; 🐝 Default: `HEAD`</small>

> The [HTTP method](https://docs.guzzlephp.org/en/stable/psr7.html#request-methods)
> used to perform cache warmup requests.

::: code-group

```bash [CLI]
./cache-warmup.phar --crawler-options '{"request_method": "GET"}'
```

```json [JSON]
{
    "crawlerOptions": {
        "request_method": "GET"
    }
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->setCrawlerOption('request_method', 'GET');

    return $config;
};
```

```yaml [YAML]
crawlerOptions:
  request_method: GET
```

```bash [.env]
CACHE_WARMUP_CRAWLER_OPTIONS='{"request_method": "GET"}'
```

:::

### `request_options`

<small>🎨 Type: `array<string, mixed>` &middot; 🐝 Default: `[]`</small>

> Additional [request options](https://docs.guzzlephp.org/en/stable/request-options.html)
> used for each cache warmup request.

::: code-group

```bash [CLI]
./cache-warmup.phar --crawler-options '{"request_options": {"delay": 500, "timeout": 10}}'
```

```json [JSON]
{
    "crawlerOptions": {
        "request_options": {
            "delay": 500,
            "timeout": 10
        }
    }
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->setCrawlerOption('request_options', [
        'delay' => 500,
        'timeout' => 10,
    ]);

    return $config;
};
```

```yaml [YAML]
crawlerOptions:
  request_options:
    delay: 500
    timeout: 10
```

```bash [.env]
CACHE_WARMUP_CRAWLER_OPTIONS='{"request_options": {"delay": 500, "timeout": 10}}'
```

:::
