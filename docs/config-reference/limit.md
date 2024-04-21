# Limit <Badge type="tip" text="0.1.0+" />

<small>📝&nbsp;Name: `limit` &middot; 🖥️&nbsp;Option: `-l`, `--limit` &middot; 🐝&nbsp;Default: `0`</small>

> Limit the number of URLs to be processed.

## Example

Pass any positive number to limit the number of processed URLs.

::: code-group

```bash [CLI]
./cache-warmup.phar -l 250
./cache-warmup.phar --limit 250
```

```json [JSON]
{
    "limit": 250
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->setLimit(250);

    return $config;
};
```

```yaml [YAML]
limit: 250
```

```bash [.env]
CACHE_WARMUP_LIMIT=250
```

:::

## Disable limit

By default, no limit is defined. This can be explicitly achieved by
passing `0` as configuration value.

::: code-group

```bash [CLI]
./cache-warmup.phar -l 0
./cache-warmup.phar --limit 0
```

```json [JSON]
{
    "limit": 0
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->disableLimit();

    return $config;
};
```

```yaml [YAML]
limit: 0
```

```bash [.env]
CACHE_WARMUP_LIMIT=0
```

:::
