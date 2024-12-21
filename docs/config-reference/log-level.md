# Log level <Badge type="tip" text="2.4+" />

<small>📝&nbsp;Name: `logLevel` &middot; 🖥️&nbsp;Option: `--log-level` &middot; 🐝&nbsp;Default: `error`</small>

> The log level used to determine which crawling results to log.

::: info
This configuration option is only respected if the [`logFile`](log-file.md)
configuration option is set.
:::

## Available log levels

According to [PSR-3](https://www.php-fig.org/psr/psr-3/), the following
log levels are available:

* `emergency`
* `alert`
* `critical`
* `error` (default; enables logging of failed crawls)
* `warning`
* `notice`
* `info` (enables logging of successful crawls)
* `debug`

## Log failed crawls: `error`

By default, only failed crawls are logged. You can explicitly configure
this by setting the log level to `error`.

::: code-group

```bash [CLI]
./cache-warmup.phar --log-level error
```

```json [JSON]
{
    "logLevel": "error"
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->setLogLevel(CacheWarmup\Log\LogLevel::ERROR);

    return $config;
};
```

```yaml [YAML]
logLevel: error
```

```bash [.env]
CACHE_WARMUP_LOG_LEVEL="error"
```

:::

## Log all crawls: `info`

You can also set the log level to `info` to log successful crawls as well.

::: code-group

```bash [CLI]
./cache-warmup.phar --log-level info
```

```json [JSON]
{
    "logLevel": "info"
}
```

```php [PHP]
use EliasHaeussler\CacheWarmup;

return static function (CacheWarmup\Config\CacheWarmupConfig $config) {
    $config->setLogLevel(CacheWarmup\Log\LogLevel::INFO);

    return $config;
};
```

```yaml [YAML]
logLevel: info
```

```bash [.env]
CACHE_WARMUP_LOG_LEVEL="info"
```

:::
