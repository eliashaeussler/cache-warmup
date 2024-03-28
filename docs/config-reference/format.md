# Format <Badge type="tip" text="2.0+" />

<small>📝 Name: `format` &middot; 🚨 Required &middot; 🖥️ Option: `-f`, `--format` &middot; 🐝 Default: `text`</small>

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
[JSON schema](https://github.com/eliashaeussler/cache-warmup/blob/main/res/cache-warmup-result.schema.json).


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
