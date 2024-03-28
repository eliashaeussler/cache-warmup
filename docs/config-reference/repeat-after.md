# Endless mode <Badge type="tip" text="2.0+" />

<small>📝 Name: `repeatAfter` &middot; 🖥️ Option: `--repeat-after` &middot; 🐝 Default: `0`</small>

> Run cache warmup in endless loop and repeat *x* seconds after each run.

::: warning IMPORTANT
If cache warmup fails, the command fails immediately and is not repeated.
To continue in case of failures, the [`allowFailures`](allow-failures.md)
configuration option must be set as well.
:::

## Example

Define after how many seconds cache warmup should be repeated.

::: code-group

```bash [CLI]
./cache-warmup.phar --repeat-after 300
```

```json [JSON]
{
    "repeatAfter": 300
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->repeatAfter(300);

    return $config;
};
```

```yaml [YAML]
repeatAfter: 300
```

```bash [.env]
CACHE_WARMUP_REPEAT_AFTER=300
```

:::
