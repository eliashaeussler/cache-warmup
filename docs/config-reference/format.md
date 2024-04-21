# Format <Badge type="tip" text="2.0+" />

<small>📝&nbsp;Name: `format` &middot; 🚨&nbsp;Required &middot; 🖥️&nbsp;Option: `-f`, `--format` &middot; 🐝&nbsp;Default: `text`</small>

> The formatter used to print the cache warmup result.

## JSON formatter: `json`

This formatter can be used to format user-oriented output as JSON object.

::: code-group

```bash [CLI]
./cache-warmup.phar -f json
./cache-warmup.phar --format json
```

```json [JSON]
{
    "format": "json"
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->useJsonFormat();

    return $config;
};
```

```yaml [YAML]
format: json
```

```bash [.env]
CACHE_WARMUP_FORMAT=json
```

:::

### Data structure

The resulting JSON object includes the following properties:

| Property            | Description                                                                                                            |
|---------------------|------------------------------------------------------------------------------------------------------------------------|
| `cacheWarmupResult` | Lists all crawled URLs, grouped by their crawling state (`failure`, `success`), and may contain `cancelled` state      |
| `messages`          | Contains all logged messages, grouped by message severity (`error`, `info`, `success`, `warning`)                      |
| `parserResult`      | Lists all parsed and excluded XML sitemaps and URLs, grouped by their parsing state (`excluded`, `failure`, `success`) |
| `time`              | Lists all tracked times during cache warmup (`crawl`, `parse`)                                                         |

The complete JSON structure can be found in the provided
[JSON schema](../../res/cache-warmup-result.schema.json).

::: details Example output
```json
{
    "cacheWarmupResult": {
        "cancelled": [
            "https://www.google.com/intl/de/forms/about/",
            "https://www.google.com/intl/cs/forms/about/",
            "https://www.google.com/intl/et/forms/about/",
            "https://www.google.com/intl/es/forms/about/",
            "https://www.google.com/intl/es-419/forms/about/"
        ],
        "failure": [
            "https://www.google.com/intl/en-gb/forms/about/"
        ],
        "success": [
            "https://www.google.com/forms/about/",
            "https://www.google.com/intl/af/forms/about/",
            "https://www.google.com/intl/ca/forms/about/",
            "https://www.google.com/intl/id/forms/about/",
            "https://www.google.com/intl/ms/forms/about/",
            "https://www.google.com/intl/da/forms/about/"
        ]
    },
    "parserResult": {
        "excluded": {
            "sitemaps": [
                "https://www.google.com/gmail/sitemap.xml"
            ]
        },
        "failure": {
            "urls": [
                "https://www.google.com/intl/zu/forms/about/"
            ]
        },
        "success": {
            "sitemaps": [
                "https://www.google.com/sitemap.xml",
                "https://www.google.com/forms/sitemaps.xml"
            ],
            "urls": [
                "https://www.google.com/forms/about/",
                "https://www.google.com/intl/af/forms/about/",
                "https://www.google.com/intl/id/forms/about/",
                "https://www.google.com/intl/ca/forms/about/",
                "https://www.google.com/intl/da/forms/about/",
                "https://www.google.com/intl/ms/forms/about/",
                "https://www.google.com/intl/en-gb/forms/about/"
            ]
        }
    },
    "time": {
        "parse": "0.18s",
        "crawl": "0.212s"
    }
}

```
:::

## Text formatter: `text`

This is the default formatter that is used if no other formatter is
explicitly configured. It writes all user-oriented output to the console.

::: code-group

```bash [CLI]
./cache-warmup.phar -f text
./cache-warmup.phar --format text
```

```json [JSON]
{
    "format": "text"
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->useTextFormat();

    return $config;
};
```

```yaml [YAML]
format: text
```

```bash [.env]
CACHE_WARMUP_FORMAT=text
```

:::
