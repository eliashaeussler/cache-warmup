# Allow failures <Badge type="tip" text="0.7.10+" />

<small>📝 Name: `allowFailures` &middot; 🖥️ Option: `--allow-failures`</small>

> Allow failures during URL crawling and exit with zero.

## Example

Enable the flag to allow any failure during crawling.

::: code-group

```bash [CLI]
./cache-warmup.phar --allow-failures
```

```json [JSON]
{
    "allowFailures": true
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->allowFailures();

    return $config;
};
```

```yaml [YAML]
allowFailures: true
```

```bash [.env]
CACHE_WARMUP_ALLOW_FAILURES=1
```

:::
