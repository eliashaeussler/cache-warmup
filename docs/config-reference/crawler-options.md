---
outline: [2,3]
---

# Crawler options <Badge type="tip" text="0.7.13+" />

<small>📝&nbsp;Name: `crawlerOptions` &middot; 🖥️&nbsp;Option: `--crawler-options`</small>

> Additional options for configurable crawlers.

::: info
These options only apply to [configurable crawlers](../api/configurable-crawler.md).
If the configured crawler does not implement the required interface, a warning is
shown.
:::

## Example

Pass crawler options in the expected input format.

::: warning IMPORTANT
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

* [`EliasHaeussler\CacheWarmup\Crawler\ConcurrentCrawler`](../../src/Crawler/ConcurrentCrawler.php)
* [`EliasHaeussler\CacheWarmup\Crawler\OutputtingCrawler`](../../src/Crawler/OutputtingCrawler.php)

The following configuration options are currently available for both crawlers:

### `client_config` <Badge type="tip" text="1.2.0+" />

<small>🎨&nbsp;Type: `array<string, mixed>` &middot; 🐝&nbsp;Default: `[]`</small>

> Optional [configuration](https://docs.guzzlephp.org/en/stable/quickstart.html#creating-a-client)
> used when instantiating a new Guzzle client.

::: code-group

```bash [CLI]
./cache-warmup.phar --crawler-options '{"client_config": {"auth": ["username", "password"]}}'
```

```json [JSON]
{
    "crawlerOptions": {
        "client_config": {
            "auth": [
                "username",
                "password"
            ]
        }
    }
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->setCrawlerOption('client_config', [
        'auth' => [
            'username',
            'password',
        ],
    ]);

    return $config;
};
```

```yaml [YAML]
crawlerOptions:
  client_config:
    auth:
      - username
      - password
```

```bash [.env]
CACHE_WARMUP_CRAWLER_OPTIONS='{"client_config": {"auth": ["username", "password"]}}'
```

:::

### `concurrency` <Badge type="tip" text="0.7.13+" />

<small>🎨&nbsp;Type: `integer` &middot; 🐝&nbsp;Default: `3`</small>

> Define how many URLs are crawled concurrently.

::: info
Internally, Guzzle's [Pool](https://docs.guzzlephp.org/en/stable/quickstart.html#concurrent-requests)
feature is used to send multiple requests  concurrently using asynchronous
requests. You may also have a look at how  this is implemented in the library's
[`RequestPoolFactory`](../../src/Http/Message/RequestPoolFactory.php).
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

### `request_headers` <Badge type="tip" text="0.7.13+" />

<small>🎨&nbsp;Type: `array<string, mixed>` &middot; 🐝&nbsp;Default: `['User-Agent' => '<default user-agent>']`</small>

> A list of [HTTP headers](https://docs.guzzlephp.org/en/stable/request-options.html#headers)
> to send with each cache warmup request.

::: info
The default User-Agent is built in
[`ConcurrentCrawlerTrait::getRequestHeaders()`](../../src/Crawler/ConcurrentCrawlerTrait.php).
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

### `request_method` <Badge type="tip" text="0.7.13+" />

<small>🎨&nbsp;Type: `string` &middot; 🐝&nbsp;Default: `HEAD`</small>

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

### `request_options` <Badge type="tip" text="2.0+" />

<small>🎨&nbsp;Type: `array<string, mixed>` &middot; 🐝&nbsp;Default: `[]`</small>

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

### `write_response_body` <Badge type="tip" text="4.0+" />

<small>🎨&nbsp;Type: `bool` &middot; 🐝&nbsp;Default: `false`</small>

> Define whether or not to write response body of crawled URLs to the corresponding
> response object.

::: warning
Enabling this option may significantly increase memory consumption during cache warmup.
:::

::: code-group

```bash [CLI]
./cache-warmup.phar --crawler-options '{"write_response_body": true}'
```

```json [JSON]
{
    "crawlerOptions": {
        "write_response_body": true
    }
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->setCrawlerOption('write_response_body', true);

    return $config;
};
```

```yaml [YAML]
crawlerOptions:
  write_response_body: true
```

```bash [.env]
CACHE_WARMUP_CRAWLER_OPTIONS='{"write_response_body": true}'
```

:::
