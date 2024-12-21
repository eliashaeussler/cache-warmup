---
outline: [2,3]
---

# Parser options <Badge type="tip" text="4.0+" />

<small>📝&nbsp;Name: `parserOptions` &middot; 🖥️&nbsp;Option: `--parser-options`</small>

> Additional config for configurable parsers.

::: info
These options only apply to [configurable parsers](../api/configurable-parser.md).
If the configured parser does not implement the required interface, a warning is
shown.
:::

## Example

Pass parser options in the expected input format.

::: warning IMPORTANT
When passing parser options as **command parameter** or **environment variable**,
make sure to pass them as **JSON-encoded string**.
:::

::: code-group

```bash [CLI]
./cache-warmup.phar --parser-options '{"request_options": {"proxy": "http://localhost:8125"}}'
```

```json [JSON]
{
    "parserOptions": {
        "request_options": {
            "proxy": "http://localhost:8125"
        }
    }
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->setParserOption('request_options', [
        'proxy' => 'http://localhost:8125',
    ]);

    return $config;
};
```

```yaml [YAML]
parserOptions:
  request_options:
    proxy: 'http://localhost:8125'
```

```bash [.env]
CACHE_WARMUP_PARSER_OPTIONS='{"request_options": {"proxy": "http://localhost:8125"}}'
```

:::

## Option Reference

The default parser is implemented as configurable parser:

* [`Xml\SitemapXmlParser`](../../src/Xml/SitemapXmlParser.php)

The following configuration options are currently available for the default parser:

### `request_headers` <Badge type="tip" text="4.0+" />

<small>🎨&nbsp;Type: `array<string, mixed>` &middot; 🐝&nbsp;Default: `['User-Agent' => '<default user-agent>']`</small>

> A list of [HTTP headers](https://docs.guzzlephp.org/en/stable/request-options.html#headers)
> to send when fetching external XML sitemaps.

::: info
The default User-Agent is built in
[`ConcurrentCrawlerTrait::getRequestHeaders()`](../../src/Crawler/ConcurrentCrawlerTrait.php).
:::

::: code-group

```bash [CLI]
./cache-warmup.phar --parser-options '{"request_headers": {"X-Foo": "bar", "User-Agent": "Foo-Crawler/1.0"}}'
```

```json [JSON]
{
    "parserOptions": {
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
    $config->setParserOption('request_headers', [
        'X-Foo' => 'bar',
        'User-Agent' => 'Foo-Parser/1.0',
    ]);

    return $config;
};
```

```yaml [YAML]
parserOptions:
  request_headers:
    X-Foo: bar
    User-Agent: 'Foo-Parser/1.0'
```

```bash [.env]
CACHE_WARMUP_PARSER_OPTIONS='{"request_headers": {"X-Foo": "bar", "User-Agent": "Foo-Parser/1.0"}}'
```

:::

### `request_options` <Badge type="tip" text="4.0+" />

<small>🎨&nbsp;Type: `array<string, mixed>` &middot; 🐝&nbsp;Default: `[]`</small>

> Additional [request options](https://docs.guzzlephp.org/en/stable/request-options.html)
> used when fetching external XML sitemaps.

::: code-group

```bash [CLI]
./cache-warmup.phar --parser-options '{"request_options": {"verify": false}}'
```

```json [JSON]
{
    "parserOptions": {
        "request_options": {
            "verify": false
        }
    }
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->setParserOption('request_options', [
        'verify' => false,
    ]);

    return $config;
};
```

```yaml [YAML]
parserOptions:
  request_options:
    verify: false
```

```bash [.env]
CACHE_WARMUP_PARSER_OPTIONS='{"request_options": {"verify": false}}'
```

:::
