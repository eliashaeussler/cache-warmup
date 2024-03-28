# Progress bar <Badge type="tip" text="0.1.0+" />

<small>📝 Name: `progress` &middot; 🖥️ Option: `-p`, `--progress`</small>

> Show a progress bar during cache warmup.

::: info
The progress bar is implicitly enabled when using a non-verbose
[formatter](format.md), e.g. `json`.
:::

## Compact style

With normal output verbosity, the progress bar is shown in compact style.

::: code-group

```bash [CLI]
./cache-warmup.phar -p
./cache-warmup.phar --progress
```

```json [JSON]
{
    "progress": true
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->enableProgressBar();

    return $config;
};
```

```yaml [YAML]
progress: true
```

```bash [.env]
CACHE_WARMUP_PROGRESS=1
```

:::

## Verbose style

You can optionally use the progress bar in verbose style by increasing output
verbosity with the `--verbose` command option.

::: code-group

```bash [CLI]
./cache-warmup.phar -p -v
./cache-warmup.phar --progress --verbose
```

:::
